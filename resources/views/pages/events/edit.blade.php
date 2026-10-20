@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Editar evento" />

    <x-common.flash />

    <form method="POST" action="{{ route('events.update', $event) }}">
        @csrf
        @method('PUT')

        @include('pages.events._form', ['submitLabel' => 'Actualizar evento'])
    </form>
@endsection
