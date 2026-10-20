@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Detalle del evento') }}" />

    <x-common.placeholder-panel title="{{ __('Detalle del evento') }}" description="{{ __('Aquí se mostrará la información del evento y la venta de boletos.') }}" />
@endsection
