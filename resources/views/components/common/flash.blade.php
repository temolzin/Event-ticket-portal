@if(session('status'))
    <div class="mb-6">
        <x-ui.alert variant="success" title="Listo" :message="session('status')" />
    </div>
@endif

@if($errors->any())
    <div class="mb-6">
        <x-ui.alert variant="error" title="Revisa el formulario" :message="$errors->first()" />
    </div>
@endif
