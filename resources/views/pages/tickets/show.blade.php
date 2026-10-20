@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Detalle del boleto') }}" />

    <x-common.placeholder-panel title="{{ __('Detalle del boleto') }}" description="{{ __('Aquí se mostrará el boleto digital con su código QR y su estado.') }}" />
@endsection
