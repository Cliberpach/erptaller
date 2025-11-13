<form id="form_create_color" method="POST" class="needs-validation">
    @csrf
    @method('POST')

    <div class="row g-3">
        <!-- Descripción -->
        <div class="col-12">
            <label for="description" class="form-label fw-bold required_field">Descripción:</label>
            <input
                type="text"
                class="form-control"
                id="description"
                name="description"
                value="{{ old('description') }}"
                required
            >
            <div class="invalid-feedback">
                Este campo es obligatorio.
            </div>
            <p class="description_error msgError mb-0"></p>
        </div>

        <!-- Color -->
        <div class="col-12">
            <label for="codigo" class="form-label fw-bold">Color:</label>
            <input
                type="color"
                class="form-control"
                id="codigo"
                name="codigo"
                value="{{ old('codigo', '#ffffff') }}"
                title="Elige un color"
            >
            <p class="codigo_error msgError mb-0"></p>
        </div>
    </div>
</form>




