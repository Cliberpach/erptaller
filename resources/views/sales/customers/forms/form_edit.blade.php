<form action="" id="formActualizarCliente" method="post">
    <div class="row">
        @csrf
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="required_field" for="type_identity_document" style="font-weight: bold;">TIPO DOCUMENTO</label>
            <select required name="type_identity_document" required class="form-select select2_form" id="type_identity_document"
                data-placeholder="Seleccionar" onchange="changeTipoDoc()">
                <option></option>
                @foreach ($types_identity_documents as $type_identity_document)
                    <option @if ($customer->type_identity_document_id == $type_identity_document->id) selected @endif value="{{ $type_identity_document->id }}">
                        {{ $type_identity_document->abbreviation }}</option>
                @endforeach
            </select>
            <span class="type_identity_document_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="nro_document" style="font-weight: bold;" class="required_field">Nro Doc</label>
            <div class="input-group mb-3">
                <button @if ($customer->type_identity_document_id != 1 && $customer->type_identity_document_id != 3) disabled @endif id="btn_consultar_documento"
                    class="btn btn-primary" type="button" id="button-addon1">
                    <i class="fa-solid fa-magnifying-glass" style="color:white;"></i>
                </button>
                <input value="{{ $customer->document_number }}" required id="nro_document" name="nro_document"
                    type="text" class="form-control" placeholder="Nro de Documento"
                    aria-label="Example text with button addon" aria-describedby="button-addon1">
            </div>
            <span class="nro_document_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="name" style="font-weight: bold;" class="required_field">Nombre</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-user"></i>
                </span>
                <input value="{{ $customer->name }}" required id="name" maxlength="160" name="name"
                    type="text" class="form-control" placeholder="Nombre" aria-label="Username"
                    aria-describedby="basic-addon1">
            </div>
            <span class="name_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="address" style="font-weight: bold;">Dirección</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-address-book"></i>
                </span>
                <input value="{{ $customer->address }}" maxlength="160" id="address" name="address" type="text"
                    class="form-control" placeholder="Dirección" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <span class="address_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="" for="phone" style="font-weight: bold;">Teléfono</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-mobile-screen"></i>
                </span>
                <input value="{{ $customer->phone }}" maxlength="20" id="phone" name="phone" type="text"
                    class="form-control" placeholder="Teléfono" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <span class="phone_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="" for="email" style="font-weight: bold;">Email</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-at"></i>
                </span>
                <input value="{{ $customer->email }}" maxlength="160" id="email" name="email" type="email"
                    class="form-control" placeholder="email" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <span class="email_error msgError" style="color:red;"></span>
        </div>

        {{-- <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <div class="d-flex align-items-center">
                <input @if ($customer->control_credito) checked @endif
                    style="font-size: 20px; width: 25px; height: 25px; border: 2px solid #559fff;"
                    class="form-check-input" type="checkbox" value="1" id="control_credito"
                    name="control_credito">
                <label class="form-check-label" for="control_credito" style="font-weight: bold;margin-left:4px;">
                    PERMITIR VENTAS AL CRÉDITO
                </label>
            </div>
            <span class="control_credito_error msgError" style="color:red;"></span>
        </div> --}}

        {{-- <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label class="" for="limite_credito" style="font-weight: bold;">LÍMITE CRÉDITO</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-dollar-sign"></i>
                </span>
                <input maxlength="15" value="{{ $customer->limite_credito }}" required id="limite_credito"
                    name="limite_credito" type="text" class="form-control inputDecimalPositivo"
                    placeholder="Límite de Crédito" aria-label="Límite de Crédito" aria-describedby="basic-addon1">
            </div>
            <span class="limite_credito_error msgError" style="color:red;"></span>
        </div> --}}

        <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
            <label class="" for="department" style="font-weight: bold;">
                DEPARTAMENTO
            </label>
            <select onchange="cambiarDepartamento(this);" name="department" class="form-select select2_form"
                id="department" data-placeholder="Seleccionar">
                <option></option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
            <span class="department_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
            <label class="" for="province" style="font-weight: bold;">
                PROVINCIA
            </label>
            <select onchange="cambiarProvincia(this);" name="province" class="form-select select2_form"
                id="province" data-placeholder="Seleccionar">
                <option></option>

            </select>
            <span class="province_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
            <label class="" for="district" style="font-weight: bold;">
                DISTRITO
            </label>
            <select name="district" class="form-select select2_form" id="district" data-placeholder="Seleccionar">
                <option></option>

            </select>
            <span class="district_error msgError" style="color:red;"></span>
        </div>

    </div>
</form>
