@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Nuevo evento') }}" />

    <x-common.placeholder-panel title="{{ __('Nuevo evento') }}" description="{{ __('Aquí se capturarán nombre, descripción, lugar, fecha, capacidad y precios.') }}" />
@endsection
