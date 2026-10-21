@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Resultado del escaneo" />

    <div class="mx-auto max-w-2xl">
        @include('pages.check-in._result')
    </div>
@endsection
