@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Bitácora de escaneos') }}" />

    <x-common.placeholder-panel title="{{ __('Bitácora de escaneos') }}" description="{{ __('Aquí se listarán todos los intentos de escaneo con su resultado.') }}" />
@endsection
