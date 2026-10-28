@if(session('status'))
    <div
        hidden
        data-swal-flash
        data-icon="success"
        data-title="Listo"
        data-message="{{ session('status') }}"
    ></div>
@endif

@if($errors->any())
    <div
        hidden
        data-swal-flash
        data-icon="error"
        data-title="Revisa el formulario"
        data-message="{{ $errors->first() }}"
    ></div>
@endif
