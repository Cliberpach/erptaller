<form id="form_create_service" method="POST">
    @csrf
    @method('POST')

    <div class="row g-3">

        <!-- Nombre -->
        <div class="col-12">
            <label for="name_mdlservice" class="form-label fw-bold required_field">Nombre:</label>
            <input type="text" class="form-control" id="name_mdlservice" name="name_mdlservice" maxlength="500"
                value="{{ old('name') }}" required>
            <div class="invalid-feedback">Este campo es obligatorio.</div>
            <p class="name_mdlservice_error msgError mb-0"></p>
        </div>

        <!-- Precio -->
        <div class="col-12">
            <label for="price_mdlservice" class="form-label fw-bold required_field">Precio:</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-dollar-sign"></i>
                </span>
                <input type="text" class="form-control inputDecimalPositivo" id="price_mdlservice" name="price_mdlservice"
                    value="{{ old('price') }}" maxlength="10" required>
            </div>
            <div class="invalid-feedback">Este campo es obligatorio.</div>
            <p class="price_mdlservice_error msgError mb-0"></p>
        </div>

        <!-- Descripción -->
        <div class="col-12">
            <label for="description_mdlservice" class="form-label fw-bold">Descripción (opcional):</label>
            <textarea class="form-control" id="description_mdlservice" name="description_mdlservice" rows="3" maxlength="300">{{ old('description_mdlservice') }}</textarea>
            <p class="description_mdlservice_error msgError mb-0"></p>
        </div>

    </div>
</form>
