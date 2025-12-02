<form method="POST" id="form_edit_service">
    @csrf

    <div class="row g-3">

        <!-- Nombre -->
        <div class="col-12">
            <label for="name_edit" class="form-label fw-bold required_field">Nombre:</label>
            <input type="text" class="form-control" name="name_edit" id="name_edit" maxlength="500"
                value="{{ old('name') }}" required>
            <div class="invalid-feedback">
                Por favor, ingresa un nombre válido.
            </div>
            <p class="name_edit_error msgError text-danger small mt-1"></p>
        </div>

        <!-- Precio -->
        <div class="col-12">
            <label for="price_edit" class="form-label fw-bold required_field">Precio:</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-dollar-sign"></i>
                </span>
                <input type="text" class="form-control inputDecimalPositivo" name="price_edit" id="price_edit"
                    value="{{ old('price') }}" maxlength="10" required>
            </div>
            <div class="invalid-feedback">
                Por favor, ingresa un precio válido.
            </div>
            <p class="price_edit_error msgError text-danger small mt-1"></p>
        </div>

        <!-- Descripción -->
        <div class="col-12">
            <label for="description_edit" class="form-label fw-bold">Descripción (opcional):</label>
            <textarea class="form-control" name="description_edit" id="description_edit" rows="3" maxlength="300">{{ old('description') }}</textarea>
            <p class="description_edit_error msgError text-danger small mt-1"></p>
        </div>

    </div>
</form>
