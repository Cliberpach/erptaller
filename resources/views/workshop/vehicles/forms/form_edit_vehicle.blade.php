<form id="form_edit_vehicle" method="POST">
    @csrf
    @method('POST')

    <p class="text-muted mb-2">
        <span class="text-danger">*</span> Los campos marcados son obligatorios.
    </p>
    <div class="row g-3">

        <!-- Cliente -->
        <div class="col-md-6">
            <label for="client_id" class="form-label fw-bold required_field">Cliente:</label><i
                class="fas fa-user-plus btn btn-warning" onclick="openMdlNewCustomer();"
                style="margin-left:4px;margin-bottom:4px;"></i>
            <select class="form-control" id="client_id" name="client_id" required>

            </select>
            <div class="invalid-feedback">
                Debe seleccionar un cliente.
            </div>
            <p class="client_id_error msgError mb-0"></p>
        </div>

        <!-- Placa + botón lupa -->
        <div class="col-md-6">
            <label for="plate" class="form-label fw-bold required_field">Placa:</label>
            <div class="input-group">
                <input type="text" class="form-control text-uppercase" id="plate" name="plate" maxlength="8"
                    minlength="6" placeholder="Ej: ABC123" required value="{{ $vehicle->plate }}">
                <button class="btn btn-outline-secondary" type="button" id="btn_search_plate" title="Buscar vehículo">
                    <i class="fas fa-search"></i>
                </button>
                <div class="invalid-feedback">
                    Debe ingresar una placa válida (máx. 8 caracteres).
                </div>
            </div>
            <p class="plate_error msgError mb-0"></p>
        </div>

        <!-- Marca - Modelo -->
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label for="model_id" class="form-label fw-bold required_field">Marca - Modelo:</label>
            <select class="form-select" id="model_id" name="model_id" required>
                <option value="">Seleccione una marca - modelo</option>
            </select>
            <div class="invalid-feedback">
                Debe seleccionar una marca.
            </div>
            <p class="model_id_error msgError mb-0"></p>
        </div>

        <!-- Año -->
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label for="year_id" class="form-label fw-bold required_field">Año:</label>
            <select class="form-select" id="year_id" name="year_id">
                <option value="">Seleccione un Año</option>
                @foreach ($years as $year)
                    <option @if ($year->id == $vehicle->year_id) selected @endif value="{{ $year->id }}">
                        {{ $year->description }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">
                Debe seleccionar un año.
            </div>
            <p class="year_id_error msgError mb-0"></p>
        </div>

        <!-- Color -->
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label for="color_id" class="form-label fw-bold required_field">Color:</label>
            <select class="form-select" id="color_id" name="color_id" required>
                <option value="">Seleccione un Color</option>
                @foreach ($colors as $color)
                    <option @if ($color->id == $vehicle->color_id) selected @endif value="{{ $color->id }}">
                        {{ $color->description }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">
                Debe seleccionar un color.
            </div>
            <p class="color_id_error msgError mb-0"></p>
        </div>

        <!-- VIN -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <label for="vin" class="form-label fw-bold">VIN:</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-car"></i>
                </span>
                <input value="{{$vehicle->vin}}" type="text" class="form-control" id="vin" name="vin" placeholder="VIN" readonly>
            </div>
            <p class="vin_error msgError mb-0"></p>
        </div>

        <!-- Serie -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <label for="serie" class="form-label fw-bold">Serie:</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-barcode"></i>
                </span>
                <input value="{{$vehicle->serie}}" type="text" class="form-control" id="serie" name="serie" placeholder="Serie" readonly>
            </div>
            <p class="serie_error msgError mb-0"></p>
        </div>

        <!-- Observación -->
        <div class="col-12">
            <label for="observation" class="form-label fw-bold">Observación:</label>
            <textarea class="form-control" id="observation" name="observation" rows="3" maxlength="300"
                placeholder="Ingrese alguna observación (máximo 300 caracteres)">{{ $vehicle->observation }}</textarea>
            <small class="text-muted d-block text-right">
                Máx. 300 caracteres
            </small>
            <p class="observation_error msgError mb-0"></p>
        </div>
    </div>
</form>
