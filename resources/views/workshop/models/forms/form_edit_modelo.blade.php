<form method="POST" id="form_edit_color">
    @csrf

    <div class="row g-3">
        <div class="col-12">
            <label for="description_edit" class="form-label fw-bold required_field">Descripción:</label>
            <input type="text" class="form-control" name="description_edit" id="description_edit"
                value="{{ old('description') }}" required>
            <div class="invalid-feedback">
                Por favor, ingresa una descripción válida.
            </div>
            <p class="description_edit_error msgError text-danger small mt-1"></p>
        </div>

        <!-- Marca -->
        <div class="col-12">
            <label for="brand_id_edit" class="form-label fw-bold required_field">Marca:</label>
            <select class="form-select" id="brand_id_edit" name="brand_id_edit" required>
                <option value="">Seleccione una marca</option>
                @foreach ($brands as $brand)
                    <option value="{{ $brand->id }}">{{ $brand->description }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">
                Debe seleccionar una marca.
            </div>
            <p class="brand_error msgError mb-0"></p>
        </div>

    </div>
</form>
