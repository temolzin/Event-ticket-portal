@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Check-in') }}" />

    <x-common.placeholder-panel title="{{ __('Check-in') }}" description="{{ __('Aquí el gestor escaneará el código QR y verá el resultado del escaneo.') }}" />
@endsection
