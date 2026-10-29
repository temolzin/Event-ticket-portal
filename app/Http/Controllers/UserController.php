<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Helpers\PermissionMatrix;
use App\Http\Requests\SaveUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $searchValue = $request->query('search');
        $roleValue = $request->query('role');
        $search = is_string($searchValue) ? trim($searchValue) : '';
        $role = is_string($roleValue) ? UserRole::tryFrom($roleValue) : null;

        $users = User::query()
            ->withCount(['tickets', 'scans'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($role, fn ($query) => $query->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $roles = $this->buildRoleOptions();

        return view('pages.users.index', [
            'title' => __('Users'),
            'users' => $users,
            'roles' => $roles,
            'adminCount' => User::query()->where('role', UserRole::Admin)->count(),
        ]);
    }

    public function create(): View
    {
        return view('pages.users.create', [
            'title' => __('Create user'),
            'roles' => $this->buildRoleOptions(),
        ]);
    }

    public function showPermissions(): View
    {
        return view('pages.users.permissions', [
            'title' => __('Permissions by role'),
            'matrix' => PermissionMatrix::groups(),
            'roles' => PermissionMatrix::roles(),
        ]);
    }

    public function store(SaveUserRequest $request): RedirectResponse
    {
        User::create($request->getUserAttributes());

        return to_route('users.index')->with('status', __('User created successfully.'));
    }

    public function edit(User $user): View
    {
        return view('pages.users.edit', [
            'title' => __('Edit user'),
            'user' => $user,
            'roles' => $this->buildRoleOptions(),
        ]);
    }

    public function update(SaveUserRequest $request, User $user): RedirectResponse
    {
        $attributes = $request->getUserAttributes();

        $error = match (true) {
            $request->user()->is($user) && $attributes['role'] !== UserRole::Admin->value => __('You cannot change your own administrator role.'),
            $user->isAdmin()
                && $attributes['role'] !== UserRole::Admin->value
                && User::query()->where('role', UserRole::Admin)->count() === 1 => __('The last administrator cannot be demoted.'),
            default => null,
        };

        return $error !== null
            ? back()->withErrors(['role' => $error])
            : $this->saveUser($user, $attributes);
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $error = match (true) {
            $request->user()->is($user) => __('You cannot delete your own account.'),
            $user->isAdmin() && User::query()->where('role', UserRole::Admin)->count() === 1 => __('The last administrator cannot be deleted.'),
            $user->tickets()->exists() || $user->scans()->exists() => __('This user has sales or check-in history and cannot be deleted.'),
            default => null,
        };

        return $error !== null
            ? back()->withErrors(['user' => $error])
            : $this->deleteUser($user);
    }

    private function buildRoleOptions(): array
    {
        return collect(UserRole::cases())
            ->mapWithKeys(fn (UserRole $role) => [$role->value => __($role->describe())])
            ->all();
    }

    private function saveUser(User $user, array $attributes): RedirectResponse
    {
        $user->update($attributes);

        return to_route('users.index')->with('status', __('User updated successfully.'));
    }

    private function deleteUser(User $user): RedirectResponse
    {
        $user->delete();

        return to_route('users.index')->with('status', __('User deleted successfully.'));
    }
}
