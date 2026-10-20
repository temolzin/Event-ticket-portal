@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="{{ __('Nuevo evento') }}" />

    <x-common.flash />

    <form method="POST" action="{{ route('events.store') }}">
        @csrf

        @include('pages.events._form', ['submitLabel' => __('Guardar evento')])
    </form>
@endsection
