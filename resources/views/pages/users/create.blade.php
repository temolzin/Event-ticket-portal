@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb :pageTitle="__('Create user')" />

    <x-common.flash />

    <div class="mx-auto max-w-3xl">
        @include('pages.users.userForm', [
            'action' => route('users.store'),
            'submitLabel' => __('Create user'),
        ])
    </div>
@endsection
