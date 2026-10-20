@if(session('status'))
    <div class="mb-6">
        <x-ui.alert variant="success" :title="__('Listo')" :message="session('status')" />
    </div>
@endif

@if($errors->any())
    <div class="mb-6">
        <x-ui.alert variant="error" :title="__('Revisa el formulario')" :message="$errors->first()" />
    </div>
@endif
