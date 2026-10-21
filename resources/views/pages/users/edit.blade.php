@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Edit user')" />

    <x-common.flash />

    <div class="mx-auto max-w-3xl">
        @include('pages.users.userForm', [
            'action' => route('users.update', $user),
            'method' => 'PUT',
            'user' => $user,
            'submitLabel' => __('Save changes'),
        ])
    </div>
@endsection
