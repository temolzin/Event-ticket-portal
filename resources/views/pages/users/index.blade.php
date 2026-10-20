@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Usuarios') }}" />

    <x-common.placeholder-panel title="{{ __('Usuarios') }}" description="{{ __('Aquí se listarán los usuarios del portal y su rol.') }}" />
@endsection
