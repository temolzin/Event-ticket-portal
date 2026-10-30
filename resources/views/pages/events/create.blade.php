@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Nuevo evento" />

    <x-common.flash />

    <form method="POST" action="{{ route('events.store') }}">
        @csrf

        @include('pages.events._form', ['submitLabel' => 'Aceptar'])
    </form>
@endsection
