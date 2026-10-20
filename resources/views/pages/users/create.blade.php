@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Nuevo usuario') }}" />

    <x-common.placeholder-panel title="{{ __('Nuevo usuario') }}" description="{{ __('Aquí se dará de alta un vendedor o gestor con su rol.') }}" />
@endsection
