@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Editar usuario') }}" />

    <x-common.placeholder-panel title="{{ __('Editar usuario') }}" description="{{ __('Aquí se podrá actualizar el nombre, correo y rol del usuario.') }}" />
@endsection
