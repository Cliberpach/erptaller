<form id="form_create_vehicle" method="POST">
    @csrf
    @method('POST')

    <p class="text-muted mb-2">
        <span class="text-danger">*</span> Los campos marcados son obligatorios.
    </p>
    <div class="row g-3">

        <!-- Cliente -->
        <div class="col-md-6">
            <label for="client_id_mdlvehicle" class="form-label fw-bold required_field">Cliente:</label>
            <select class="form-control" id="client_id_mdlvehicle" name="client_id_mdlvehicle" required>
                <option value="">Seleccione un cliente</option>
            </select>
            <div class="invalid-feedback">
                Debe seleccionar un cliente.
            </div>
            <p class="client_id_mdlvehicle_error msgError mb-0"></p>
        </div>

        <!-- Placa + botón lupa -->
        <div class="col-md-6">
            <label for="plate" class="form-label fw-bold required_field">Placa:</label>
            <div class="input-group">
                <input type="text" class="form-control text-uppercase" id="plate_mdlvehicle" name="plate_mdlvehicle"
                    maxlength="8" minlength="6" placeholder="Ej: ABC123" required>
                <button class="btn btn-outline-secondary" type="button" id="btn_search_plate" title="Buscar vehículo">
                    <i class="fas fa-search"></i>
                </button>
                <div class="invalid-feedback">
                    Debe ingresar una placa válida (máx. 8 caracteres).
                </div>
            </div>
            <p class="plate_mdlvehicle_error msgError mb-0"></p>
        </div>

        <!-- Marca - Modelo -->
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label for="model_id_mdlvehicle" class="form-label fw-bold required_field">Marca - Modelo:</label>
            <select class="form-select" id="model_id_mdlvehicle" name="model_id_mdlvehicle" required>
                <option value="">Seleccione una marca - modelo</option>
            </select>
            <div class="invalid-feedback">
                Debe seleccionar una marca.
            </div>
            <p class="model_id_mdlvehicle_error msgError mb-0"></p>
        </div>

        <!-- Año -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <label for="year_id_mdlvehicle" class="form-label fw-bold">Año:</label>
            <select class="form-select" id="year_id_mdlvehicle" name="year_id_mdlvehicle">
                <option value="">Seleccionar</option>
                @foreach ($years as $year)
                    <option value="{{ $year->id }}">{{ $year->description }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">
                Debe seleccionar un año.
            </div>
            <p class="year_id_mdlvehicle_error msgError mb-0"></p>
        </div>

        <!-- Color -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <label for="color_id_mdlvehicle" class="form-label fw-bold required_field">Color:</label>
            <select class="form-select" id="color_id_mdlvehicle" name="color_id_mdlvehicle" required>
                <option value="">Seleccionar</option>
                @foreach ($colors as $color)
                    <option value="{{ $color->id }}">{{ $color->description }}</option>
                @endforeach
            </select>
            <div class="invalid-feedback">
                Debe seleccionar un color.
            </div>
            <p class="color_id_mdlvehicle_error msgError mb-0"></p>
        </div>

        <!-- VIN -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12">
            <label for="vin" class="form-label fw-bold">VIN:</label>
            <div class="input-group">
                <span class="input-group-text">
                    <i class="fas fa-car"></i>
                </span>
                <input type="text" class="form-control" id="vin" name="vin" placeholder="VIN" readonly>
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
                <input type="text" class="form-control" id="serie" name="serie" placeholder="Serie" readonly>
            </div>
            <p class="serie_error msgError mb-0"></p>
        </div>

        <!-- Observación -->
        <div class="col-12">
            <label for="observation_mdlvehicle" class="form-label fw-bold">Observación:</label>
            <textarea class="form-control" id="observation_mdlvehicle" name="observation_mdlvehicle" rows="3" maxlength="300"
                placeholder="Ingrese alguna observación (máximo 300 caracteres)"></textarea>
            <small class="text-muted d-block text-right">
                Máx. 300 caracteres
            </small>
            <p class="observation_mdlvehicle_error msgError mb-0"></p>
        </div>
    </div>
</form>
