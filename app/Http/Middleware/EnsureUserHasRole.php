<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowed = array_values(array_filter(
            array_map(fn (string $role) => UserRole::tryFrom($role), $roles)
        ));

        $user = $request->user();

        abort_unless(
            $user !== null && in_array($user->role, $allowed, true),
            403
        );

        return $next($request);
    }
}
