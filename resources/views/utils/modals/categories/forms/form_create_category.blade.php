<form id="form_create_category" method="POST">
    @csrf
    @method('POST')

    <div class="row g-3">

        <!-- Nombre -->
        <div class="col-12">
            <label for="name_mdlcategory" class="form-label fw-bold required_field">Nombre:</label>
            <input type="text" class="form-control" id="name_mdlcategory" name="name_mdlcategory" maxlength="500"
                value="{{ old('name') }}" required>
            <div class="invalid-feedback">Este campo es obligatorio.</div>
            <p class="name_mdlcategory_error msgError mb-0"></p>
        </div>

    </div>
    
</form>
