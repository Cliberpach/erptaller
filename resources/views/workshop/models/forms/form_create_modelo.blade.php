<form id="form_create_color" method="POST" class="needs-validation" >
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

        <!-- Marca -->
        <div class="col-12">
            <label for="brand_id" class="form-label fw-bold required_field">Marca:</label>
            <select
                class="form-select"
                id="brand_id"
                name="brand_id"
                required
            >
                <option value="">Seleccione una marca</option>
                @foreach($brands as $brand)
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
