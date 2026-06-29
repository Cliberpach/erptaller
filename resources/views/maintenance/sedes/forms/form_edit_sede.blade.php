<form action="" id="formActualizarSede" method="post">
    <div class="row">
        @csrf
        <input type="hidden" id="sede_id_edit" name="sede_id_edit">

        <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
            <label for="nombre_edit" style="font-weight: bold;" class="required_field">Nombre</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-building"></i></span>
                <input required id="nombre_edit" name="nombre_edit" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Nombre de la sede">
            </div>
            <span class="nombre_edit_error msgError_edit" style="color:red;"></span>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label for="codigo_edit" style="font-weight: bold;" class="required_field">Código</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                <input required id="codigo_edit" name="codigo_edit" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Ej. S002">
            </div>
            <span class="codigo_edit_error msgError_edit" style="color:red;"></span>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
            <label for="direccion_edit" style="font-weight: bold;">Dirección</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                <input id="direccion_edit" name="direccion_edit" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Dirección de la sede">
            </div>
            <span class="direccion_edit_error msgError_edit" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <label for="telefono_edit" style="font-weight: bold;">Teléfono</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                <input id="telefono_edit" name="telefono_edit" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Teléfono">
            </div>
            <span class="telefono_edit_error msgError_edit" style="color:red;"></span>
        </div>

        {{-- Ubigeo: 3 selects encadenados (mismo patrón que Registrar Cliente).
             Solo el distrito se persiste -> name="ubigeo_edit" (código de 6 dígitos). --}}
        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label for="department_edit" style="font-weight: bold;">Departamento</label>
            <select name="department_edit" class="form-select select2_form_sede_edit" id="department_edit"
                data-placeholder="Seleccionar" onchange="changeDepartmentSedeEdit(this.value)">
                <option value="">Seleccionar</option>
                @foreach ($departments as $department)
                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label for="province_edit" style="font-weight: bold;">Provincia</label>
            <select name="province_edit" class="form-select select2_form_sede_edit" id="province_edit"
                data-placeholder="Seleccionar" onchange="changeProvinceSedeEdit(this.value)">
                <option value="">Seleccionar</option>
            </select>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label for="ubigeo_edit" style="font-weight: bold;">Distrito</label>
            <select name="ubigeo_edit" class="form-select select2_form_sede_edit" id="ubigeo_edit"
                data-placeholder="Seleccionar">
                <option value="">Seleccionar</option>
            </select>
            <span class="ubigeo_edit_error msgError_edit" style="color:red;"></span>
        </div>
    </div>
</form>
