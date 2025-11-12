<form id="formEditCompanyTenant" action="{{ route('tenant.mantenimientos.empresa.update', $company->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="ruc" class="form-label">RUC</label>
            <input type="text" class="form-control" id="ruc" name="ruc" value="{{ $company->ruc }}" readonly>
        </div>
        <div class="col-md-6">
            <label for="business_name" class="form-label">Razón Social</label>
            <input type="text" class="form-control" id="business_name" name="business_name" value="{{ $company->business_name }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <label for="abbreviated_business_name" class="form-label">Razón Social Abreviada</label>
            <input type="text" class="form-control" id="abbreviated_business_name" name="abbreviated_business_name" value="{{ $company->abbreviated_business_name }}">
        </div>
        <div class="col-md-6">
            <label for="fiscal_address" class="form-label">Dirección Fiscal</label>
            <input type="text" class="form-control" id="fiscal_address" name="fiscal_address" value="{{ $company->fiscal_address }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="phone" class="form-label">Teléfono</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ $company->phone }}">
        </div>
        <div class="col-md-4">
            <label for="cellphone" class="form-label">Celular</label>
            <input type="text" class="form-control" id="cellphone" name="cellphone" value="{{ $company->cellphone }}">
        </div>
        <div class="col-md-4">
            <label for="zip_code" class="form-label">Código Postal</label>
            <input type="text" class="form-control" id="zip_code" name="zip_code" value="{{ $company->zip_code }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="email" class="form-label">Correo Electrónico</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ $company->email }}">
        </div>
        <div class="col-md-4">
            <label for="facebook" class="form-label">Facebook</label>
            <input type="text" class="form-control" id="facebook" name="facebook" value="{{ $company->facebook }}">
        </div>
        <div class="col-md-4">
            <label for="instagram" class="form-label">Instagram</label>
            <input type="text" class="form-control" id="instagram" name="instagram" value="{{ $company->instagram }}">
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-4">
            <label for="web" class="form-label">Página Web</label>
            <input type="text" class="form-control" id="web" name="web" value="{{ $company->web }}">
        </div>
        <div class="col-md-4">
            <label for="invoicing_status" class="form-label">Estado de Facturación</label>
            <select class="form-select" id="invoicing_status" name="invoicing_status">
                <option value="0" {{ $company->invoicing_status == '0' ? 'selected' : '' }}>Inactivo</option>
                <option value="1" {{ $company->invoicing_status == '1' ? 'selected' : '' }}>Activo</option>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label for="input-logo" class="form-label">LOGO</label>
            <input class="form-control image" accept="image/*" type="file" id="input-logo" name="logo">
        </div>

        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label class="required_field" for="department" style="font-weight: bold;">DEPARTAMENTO</label>
            <select required name="department" required class="form-select select2_form" id="department" data-placeholder="Seleccionar" onchange="changeDepartment(this.value)">
                <option></option>
                @foreach ($departments as $department)
                    <option @if ($company_invoice->department_id == $department->id)
                        selected
                    @endif value="{{$department->id}}">{{$department->name}}</option>
                @endforeach
            </select>
            <span class="department_error_customer msgErrorCustomer"  style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label class="required_field" for="province" style="font-weight: bold;">PROVINCIA</label>
            <select required name="province" required class="form-select select2_form" id="province" data-placeholder="Seleccionar" onchange="changeProvince(this.value)">
                <option></option>
            </select>
            <span class="province_error_customer msgErrorCustomer"  style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label class="required_field" for="district" style="font-weight: bold;">DISTRITO</label>
            <select required name="district" required class="form-select select2_form" id="district" data-placeholder="Seleccionar">
                <option></option>
            </select>
            <span class="district_customer msgErrorCustomer"  style="color:red;"></span>
        </div>

        <div class="col-12">
            <label for="map" class="form-label" style="font-weight: bold;">UBICACIÓN</label>
            <div>
                <input id="searchBox" type="text" placeholder="Buscar dirección..." style="width: 100%; padding: 10px; margin-bottom: 10px;">
            </div>
            <div id="map" style="width:100%;height:300px;">
            </div>
            <input type="hidden" id="lat" name="lat">
            <input type="hidden" id="lng" name="lng">
        </div>
    </div>

    <!-- Mostrar logo actual -->
    @if($company->logo_url)
        <div class="row mb-3">
            <div class="col-md-12 text-center">
                <div class="container-img">
                    <img src="{{ asset($company->logo_url) }}" id="preview-logo" alt="Logo Actual" class="logo-preview">
                    <span class="delete-image">Quitar imagen</span>
                </div>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <a href="{{ route('tenant.mantenimientos.empresa') }}" class="btn btn-secondary ms-2">Cancelar</a>
    </div>
</form>