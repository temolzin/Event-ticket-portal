@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Boletos cancelados') }}" />

    <x-common.placeholder-panel title="{{ __('Boletos cancelados') }}" description="{{ __('Aquí se listarán los boletos cancelados: quién canceló, de qué evento y qué vendedor lo registró, para no volver a invitar.') }}" />
@endsection
