@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Panel') }}" />

    <x-common.placeholder-panel
        title="{{ __('Panel') }}"
        description="{{ __('Bienvenido al portal de boletos. Usa el menú lateral para navegar entre los módulos.') }}"
    />
@endsection
