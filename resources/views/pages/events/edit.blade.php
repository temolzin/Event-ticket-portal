@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Editar evento') }}" />

    <x-common.placeholder-panel title="{{ __('Editar evento') }}" description="{{ __('Aquí se podrá actualizar la información del evento.') }}" />
@endsection
