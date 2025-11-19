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

    </div>
</form>
