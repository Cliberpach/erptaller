<form action="" id="formRegistrarColaborador" method="post">
    <div class="row">
        @csrf
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="required_field" for="document_type" style="font-weight: bold;">TIPO DOCUMENTO</label>
            <select required name="document_type" required class="select2_form form-select" id="document_type"
                data-placeholder="Seleccionar" onchange="changeTipoDoc()">
                <option></option>
                @foreach ($tipos_documento as $tipo_documento)
                    @if ($tipo_documento->id != 40)
                        <option value="{{ $tipo_documento->id }}">{{ $tipo_documento->name }}</option>
                    @endif
                @endforeach
            </select>
            <span class="document_type_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="document_number" style="font-weight: bold;" class="required_field">Nro Doc</label>
            <div class="input-group mb-3">
                <button id="btn_consultar_documento" disabled class="btn btn-primary" type="button" id="button-addon1">
                    <i class="fa-solid fa-magnifying-glass" style="color:white;"></i>
                </button>
                <input required readonly id="document_number" name="document_number" type="text" class="form-control"
                    placeholder="Nro de Documento" aria-label="Example text with button addon"
                    aria-describedby="button-addon1">
            </div>
            <span class="document_number_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="full_name" style="font-weight: bold;" class="required_field">Nombre</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-user"></i>
                </span>
                <input required id="full_name" maxlength="260" name="full_name" type="text" class="form-control"
                    placeholder="Nombre" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <span class="full_name_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="required_field" for="position_id " style="font-weight: bold;">CARGO</label>
            <select required name="position" required class="select2_form form-select" id="position"
                data-placeholder="Seleccionar">
                <option></option>
                @foreach ($cargos as $cargo)
                    <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                @endforeach
            </select>
            <span class="position_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="address" style="font-weight: bold;">Dirección</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-address-book"></i>
                </span>
                <input maxlength="200" id="address" name="address" type="text" class="form-control"
                    placeholder="Dirección" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <span class="address_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="required_field" for="phone" style="font-weight: bold;">Teléfono</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-mobile-screen"></i>
                </span>
                <input maxlength="20" id="phone" name="phone" type="text" class="form-control"
                    placeholder="Teléfono" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <span class="phone_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="required_field" for="work_days" style="font-weight: bold;">Días Trabajo</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-clock"></i>
                </span>
                <input required maxlength="20" id="work_days" name="work_days" type="text"
                    class="form-control" placeholder="Días de trabajo" aria-label="Username"
                    aria-describedby="basic-addon1">
            </div>
            <span class="work_days_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="required_field" for="rest_days" style="font-weight: bold;">Días Descanso</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-clock"></i>
                </span>
                <input required maxlength="20" id="rest_days" name="rest_days" type="text"
                    class="form-control" placeholder="Días de descanso" aria-label="Username"
                    aria-describedby="basic-addon1">
            </div>
            <span class="rest_days_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="required_field" for="monthly_salary" style="font-weight: bold;">Pago Mensual</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-money-bill-1-wave"></i>
                </span>
                <input required maxlength="10" name="monthly_salary" id="monthly_salary" type="text"
                    class="form-control" placeholder="Pago mensual" aria-label="Username"
                    aria-describedby="basic-addon1">
            </div>
            <span class="monthly_salary_error msgError" style="color:red;"></span>
        </div>
    </div>
</form>
