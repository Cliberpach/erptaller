<form id="form_create_color" method="POST" class="needs-validation">
    @csrf
    @method('POST')

    <div class="row g-3">

        <!-- Descripción -->
        <div class="col-12">
            <label for="description" class="form-label fw-bold required_field">Descripción:</label>
            <input type="text" class="form-control" id="description" name="description" value="{{ old('description') }}"
                required>
            <div class="invalid-feedback">
                Este campo es obligatorio.
            </div>
            <p class="description_error msgError mb-0"></p>
        </div>

        <!-- Marca - Modelo -->
        <div class="col-12">
            <label for="model_id" class="form-label fw-bold required_field">Marca - Modelo:</label>
            <select class="form-select" id="model_id" name="model_id" required>
                <option value="">Seleccione una marca - modelo</option>
            </select>
            <div class="invalid-feedback">
                Debe seleccionar una marca.
            </div>
            <p class="model_id_error msgError mb-0"></p>
        </div>

    </div>
</form>
