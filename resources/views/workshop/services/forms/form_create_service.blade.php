<form id="form_create_service" method="POST">
    @csrf
    @method('POST')

    <div class="row g-3">

        <!-- Nombre -->
        <div class="col-12">
            <label for="name" class="form-label fw-bold required_field">Nombre:</label>
            <input type="text" class="form-control" id="name" name="name" maxlength="500"
                value="{{ old('name') }}" required>
            <div class="invalid-feedback">Este campo es obligatorio.</div>
            <p class="name_error msgError mb-0"></p>
        </div>

        <!-- Precio -->
        <div class="col-12">
            <label for="price" class="form-label fw-bold required_field">Precio:</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-dollar-sign"></i>
                </span>
                <input type="text" class="form-control inputDecimalPositivo" id="price" name="price"
                    value="{{ old('price') }}" maxlength="10" required>
            </div>
            <div class="invalid-feedback">Este campo es obligatorio.</div>
            <p class="price_error msgError mb-0"></p>
        </div>

        <!-- Descripción -->
        <div class="col-12">
            <label for="description" class="form-label fw-bold">Descripción (opcional):</label>
            <textarea class="form-control" id="description" name="description" rows="3" maxlength="300">{{ old('description') }}</textarea>
            <p class="description_error msgError mb-0"></p>
        </div>

    </div>
</form>
