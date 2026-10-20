@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Boletos') }}" />

    <x-common.placeholder-panel title="{{ __('Boletos') }}" description="{{ __('Aquí se listarán los boletos vendidos, con su estado y comprador.') }}" />
@endsection
