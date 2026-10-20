@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Nuevo boleto') }}" />

    <x-common.placeholder-panel title="{{ __('Nuevo boleto') }}" description="{{ __('Aquí se capturarán los datos del comprador, el tipo de boleto y el comprobante de pago.') }}" />
@endsection
