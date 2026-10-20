@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Eventos') }}" />

    <x-common.placeholder-panel title="{{ __('Eventos') }}" description="{{ __('Aquí se listarán los eventos, con su capacidad, precio y estado.') }}" />
@endsection
