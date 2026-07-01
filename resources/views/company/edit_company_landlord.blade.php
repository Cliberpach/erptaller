@extends('layouts.template')

@section('title')
    Editar Empresa
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <form id="form-company-edit" action="{{ route('landlord.mantenimientos.empresas.update', ['id' => $tenant_data->id]) }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills mb-3" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-top-empresa" aria-controls="navs-pills-top-empresa"
                            aria-selected="true">
                            Empresa
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-pills-top-banco" aria-controls="navs-pills-top-banco"
                            aria-selected="false">
                            Módulos
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade active show" id="navs-pills-top-empresa" role="tabpanel">
                        {{-- empresa --}}
                        <div class="row">
                            @if (session('error'))
                                <div class="alert alert-danger">
                                    {{ session('error') }}
                                </div>
                            @endif
                            <div class="col-sm-6 col-12" style="border-right: 1px solid #e7eaec;">
                                <label class="form-label" for="domain">Hostname:</label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control @error('domain') is-invalid @enderror"
                                        placeholder="Nombre del dominio" id="domain" name="domain"
                                        value="{{ $tenant_data->domain }}"
                                        readonly>
                                    <span class="input-group-text">.tallersuite.store</span>
                                    <br>
                                </div>
                                @error('domain')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="ruc">RUC:</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control @error('ruc') is-invalid @enderror"
                                                id="ruc" name="ruc" placeholder="Número de ruc"
                                                value="{{ $tenant_data->ruc }}">
                                            <button class="btn btn-outline-primary" type="button" id="btn_consulta_sunat"
                                                style="padding-right: 10px; padding-left: 10px;"><i
                                                    class="bx bx-search"></i> Sunat</button>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label" for="estado">Estado:</label>
                                        <input type="text" class="form-control text-center" id="estado" name="estado"
                                            readonly value="SIN VERIFICAR">
                                    </div>
                                </div>
                                @error('ruc')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror

                                <div class="mb-3">
                                    <label class="form-label" for="razon_social">Razón social:</label>
                                    <input type="text" class="form-control @error('razon_social') is-invalid @enderror"
                                        id="razon_social" name="razon_social" value="{{ $tenant_data->business_name }}">
                                </div>
                                @error('razon_social')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror

                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="razon_social_abreviada">Razón Social
                                            Abreviada:</label>
                                        <input type="text"
                                            class="form-control @error('razon_social_abreviada') is-invalid @enderror"
                                            id="razon_social_abreviada" name="razon_social_abreviada"
                                            value="{{ $tenant_data->abbreviated_business_name }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-bold" for="input-logo">
                                            <i class="fas fa-image text-primary me-1"></i> Logo
                                        </label>
                                        <input class="form-control" type="file" name="logo" id="input-logo"
                                            accept="image/jpeg, image/webp">
                                        @if ($tenant_data->logo_url)
                                            <img id="show-logo" src="{{ asset($tenant_data->logo_url) }}"
                                                style="max-height: 60px; margin-top: 6px;">
                                        @endif
                                    </div>
                                </div>
                                @error('razon_social_abreviada')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror

                                <div class="row mb-3">
                                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                                        <label class="required_field" for="department" style="font-weight: bold;">DEPARTAMENTO</label>
                                        <select required name="department" id="department" data-placeholder="Seleccionar"
                                            onchange="changeDepartment(this.value)">
                                            <option></option>
                                            @foreach ($departments as $department)
                                                <option @if ($tenant_data->department_id == $department->id) selected @endif
                                                    value="{{ $department->id }}">{{ $department->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="department_error msgError" style="color:red;"></span>
                                    </div>

                                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                                        <label class="required_field" for="province" style="font-weight: bold;">PROVINCIA</label>
                                        <select required name="province" id="province" data-placeholder="Seleccionar"
                                            onchange="changeProvince(this.value)">
                                            <option></option>
                                        </select>
                                        <span class="province_error msgError" style="color:red;"></span>
                                    </div>

                                    <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                                        <label class="required_field" for="district" style="font-weight: bold;">DISTRITO</label>
                                        <select required name="district" id="district" data-placeholder="Seleccionar">
                                            <option></option>
                                        </select>
                                        <span class="district_error msgError" style="color:red;"></span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="direccion_fiscal" class="form-label">Dirección Fiscal</label>
                                    <textarea class="form-control" id="direccion_fiscal" name="direccion_fiscal" rows="2">{{ $tenant_data->fiscal_address }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="correo">Correo:</label>
                                            <input type="email" class="form-control" id="correo" name="correo"
                                                value="{{ $user->email }}">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <div class="form-password-toggle">
                                                <label class="form-label" for="password">Password</label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" class="form-control" id="password"
                                                        name="password" value="{{ $user->password_visible }}">
                                                    <span class="input-group-text cursor-pointer"><i
                                                            class="bx bx-hide"></i></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-12">
                                <p>Facturación Electrónica</p>
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="secondary_user">Usuario Secundario:</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class='bx bx-user'></i></span>
                                            <input type="text" class="form-control" placeholder="Usuario secundario"
                                                id="secondary_user" name="secondary_user"
                                                value="{{ $tenant_data->secondary_user }}">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-password-toggle">
                                            <label class="form-label" for="secondary_user">Clave de Usuario
                                                secundario:</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class='bx bx-shield'></i></span>
                                                <input type="text" class="form-control" id="secondary_password"
                                                    name="secondary_password" value="{{ $tenant_data->secondary_password }}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-12 mb-3">
                                        <label class="fw-bold" for="api_user_gre">USUARIO GUÍAS REMISIÓN</label>
                                        <div class="input-group">
                                            <span class="input-group-text text-primary"><i class="fas fa-user-lock"></i></span>
                                            <input value="{{ $tenant_data->api_user_gre }}" id="api_user_gre"
                                                name="api_user_gre" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-12 mb-3">
                                        <label class="fw-bold" for="api_pass_gre">CLAVE GUÍAS REMISIÓN</label>
                                        <div class="input-group">
                                            <span class="input-group-text text-success"><i class="fas fa-key"></i></span>
                                            <input value="{{ $tenant_data->api_password_gre }}" id="api_pass_gre"
                                                name="api_pass_gre" type="text" class="form-control">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="certificate">Certificado:</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="certificate" name="certificate"
                                            accept=".pem,.p12">
                                    </div>
                                    <div class="text-dark mt-1">
                                        {{ $tenant_data->certificate ?: 'SIN CERTIFICADO' }}
                                    </div>
                                    <small class="text-primary fst-italic">
                                        Formatos permitidos: <b>.PEM</b> o <b>.P12</b>. Si sube <b>.P12</b>, se
                                        convertirá automáticamente a <b>.PEM</b>.
                                    </small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="certificate_password">Contraseña de
                                        Certificado:</label>
                                    <div class="input-group">
                                        <textarea class="form-control" id="certificate_password" name="certificate_password" rows="5">{{ $tenant_data->certificate_password }}</textarea>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="certificate_password">
                                        <span class="me-2">Plan</span>
                                        <button type="button" class="btn btn-outline-primary me-1"
                                            data-bs-toggle="modal" data-bs-target="#modal_create_plan">
                                            Nuevo Plan
                                        </button>
                                    </label>
                                    <div class="input-group">
                                        <select name="plan_id" id="plan_id" class="form-select @error('plan_id') is-invalid @enderror">
                                            <option value="">Seleccione ...</option>
                                            @foreach ($plans as $plan)
                                                <option
                                                @if ($plan->id == $tenant_data->plan)
                                                    selected
                                                @endif
                                                value="{{ $plan->id }}">{{ $plan->description }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                @error('plan_id')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror

                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="navs-pills-top-banco" role="tabpanel">
                        {{-- modulo --}}
                        <p>Módulos</p>

                        <div class="row">
                            @foreach ($all_modules as $module)
                                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                                    <div class="card h-100 mb-4">
                                        <div class="card-body">
                                            <div class="card-text mb-2">
                                                <div class="form-check">
                                                    <input class="form-check-input module-checkbox" type="checkbox"
                                                        id="module{{ $module->id }}" name="module_id[]"
                                                        value="{{ $module->id }}"
                                                        @if ($tenant_modules->contains('id', $module->id))
                                                            checked
                                                        @endif>
                                                    <label class="form-check-label" for="module{{ $module->id }}">
                                                        {{ $module->description }}
                                                    </label>
                                                </div>
                                            </div>
                                            @foreach ($module->children as $child)
                                                <div class="card-text mb-2" style="margin-left: 1.5rem;">
                                                    <div class="form-check">
                                                        <input
                                                            class="form-check-input {{ $child->grandchildren->isNotEmpty() ? 'child-grandchild-checkbox' : 'child-checkbox' }}"
                                                            type="checkbox" id="children{{ $child->id }}"
                                                            name="child_id[]" value="{{ $child->id }}"
                                                            @if ($tenant_modules_children->contains('id', $child->id))
                                                                checked
                                                            @endif>
                                                        <label class="form-check-label"
                                                            for="children{{ $child->id }}">
                                                            {{ $child->description }}
                                                        </label>
                                                    </div>
                                                </div>
                                                @foreach ($child->grandchildren as $grandchild)
                                                    <div class="card-text {{ $loop->last ? 'mb-2' : '' }}"
                                                        style="margin-left: 2.5rem">
                                                        <div class="form-check">
                                                            <input class="form-check-input grandchild-checkbox"
                                                                type="checkbox" id="grandchildren{{ $grandchild->id }}"
                                                                name="grandchild_id[]" value="{{ $grandchild->id }}"
                                                                @if ($tenant_modules_grand_children->contains('id', $grandchild->id))
                                                                    checked
                                                                @endif>
                                                            <label class="form-check-label"
                                                                for="grandchildren{{ $grandchild->id }}">
                                                                {{ $grandchild->description }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-outline-primary me-1">Registrar</button>
                        <a href="{{ route('landlord.mantenimientos.empresa') }}"
                            class="btn btn-outline-secondary me-1">Regresar</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="modal_create_plan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="frm_plan">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Nuevo Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-notepad'></i></span>
                                    <input type="text" class="form-control" placeholder="Descripción del plan"
                                        id="description" name="description">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="description_error"></p>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="number_fields" class="form-label">Número de Campos</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-dots-horizontal-rounded'></i></span>
                                    <input type="text" class="form-control"
                                        placeholder="Número de campos permitodos en el plan" id="number_fields"
                                        name="number_fields">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="number_fields_error"></p>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="price" class="form-label">Precio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-dollar'></i></span>
                                    <input type="text" class="form-control" placeholder="Precio del plan"
                                        id="price" name="price">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="price_error"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
@endsection

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).on('click', '#btn_consulta_sunat', function() {
            const user_ruc = $('#ruc').val();
            if (user_ruc.length == 11) {
                Swal.fire({
                    title: 'Consultar',
                    text: "¿Desea consultar RUC a Sunat?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: "#696cff",
                    confirmButtonText: 'Si, Confirmar',
                    cancelButtonText: "No, Cancelar",
                    showLoaderOnConfirm: true,
                    preConfirm: function() {
                        var url = '/landlord/ruc/' + user_ruc;
                        return $.ajax({
                            url: url,
                            type: 'GET',
                            dataType: 'json'
                        });
                    },
                    allowOutsideClick: function() {
                        return !Swal.isLoading();
                    }
                }).then(function(data) {
                    if (data.value.success === false) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'RUC inválido o no existe!'
                        });
                        $('#estado').val(data.value.message);
                        $('#razon_social').val('');
                        $('#razon_social_abreviada').val('');
                    } else {
                        $('#estado').val(data.value.data.estado);
                        $('#razon_social').val(data.value.data.nombre_o_razon_social);
                        $('#razon_social_abreviada').val(data.value.data.nombre_o_razon_social);
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'El RUC debe contener 11 dígitos'
                });
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.module-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const childCheckboxes = cardBody.find('.child-checkbox, .child-grandchild-checkbox');
                const grandchildCheckboxes = cardBody.find('.grandchild-checkbox');

                if ($(this).prop('checked')) {
                    childCheckboxes.prop('checked', true);
                    grandchildCheckboxes.prop('checked', true);
                } else {
                    childCheckboxes.prop('checked', false);
                    grandchildCheckboxes.prop('checked', false);
                }
            });

            $('.child-checkbox, .child-grandchild-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const moduleCheckbox = cardBody.find('.module-checkbox');

                if ($(this).prop('checked')) {
                    moduleCheckbox.prop('checked', true);
                } else {
                    let allCheckboxes = cardBody.find('.child-checkbox, .child-grandchild-checkbox');

                    if (allCheckboxes.filter(':checked').length === 0) {
                        moduleCheckbox.prop('checked', false);
                    }
                }
            });

            $('.grandchild-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const childGrandchildCheckboxes = cardBody.find('.child-grandchild-checkbox');
                const childCheckboxes = cardBody.find('.child-checkbox');
                const moduleCheckboxes = cardBody.find('.module-checkbox');

                if ($(this).prop('checked')) {

                    childGrandchildCheckboxes.prop('checked', true);
                    moduleCheckboxes.prop('checked', true);

                } else {
                    let allGrandchildCheckboxes = cardBody.find('.grandchild-checkbox');

                    if (allGrandchildCheckboxes.filter(':checked').length === 0) {
                        childGrandchildCheckboxes.prop('checked', false);

                        if (childCheckboxes.filter(':checked').length === 0)
                            moduleCheckboxes.prop('checked', false);
                    }
                }
            });

            $('.child-grandchild-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const grandchildCheckboxes = cardBody.find('.grandchild-checkbox');

                if ($(this).prop('checked')) {
                    grandchildCheckboxes.prop('checked', true);
                } else {
                    grandchildCheckboxes.prop('checked', false);
                }
            });
        });
    </script>

    <script>
        $("#frm_plan").on("submit", function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route('landlord.mantenimientos.planes.store') }}',
                method: 'POST',
                dataType: 'json',
                data: new FormData($("#frm_plan")[0]),
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#btn_guardar').attr("disabled", true);
                    $('#btn_guardar').html(
                        '<div class="spinner-border spinner-border-sm text-white" role="status"></div> Guardando...'
                    );
                },
                success: function(data) {
                    $('#modal_create_plan').modal('hide');
                    $('#frm_plan')[0].reset();
                    toastr.success(data.message, 'Crear Registro', {
                        timeOut: 3000
                    });

                    let select = $('#plan_id');
                    select.empty();

                    $.each(data.plans, function(index, plan) {
                        select.append($('<option>', {
                            value: plan.id,
                            text: plan.description
                        }));
                    });

                    $('#description_error').text('');
                    $('#number_fields_error').text('');
                    $('#price_error').text('');
                },
                error: function(data) {
                    let errores = data.responseJSON.errors;

                    errores.hasOwnProperty('description') ? $('#description_error').text(
                        `* ${errores.description[0]}`) : $('#description_error').text('');

                    errores.hasOwnProperty('number_fields') ? $('#number_fields_error').text(
                        `* ${errores.number_fields[0]}`) : $('#number_fields_error').text('');

                    errores.hasOwnProperty('price') ? $('#price_error').text(`* ${errores.price[0]}`) :
                        $('#price_error').text('');

                    $('#btn_guardar').text('Registrar');
                    $('#btn_guardar').attr("disabled", false);
                },
                complete: function() {
                    $('#btn_guardar').text('Guardar');
                    $('#btn_guardar').attr("disabled", false);
                },
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            loadTomSelect();
            setUbigeoPreview();
            events();
        })

        function events() {
            document.querySelector('#form-company-edit').addEventListener('submit', (e) => {
                e.preventDefault();
                updateCompany(e.target);
            })
        }

        function updateCompany(formUpdateCompany) {
            Swal.fire({
                title: '¿Desea actualizar la empresa?',
                text: "¡Se actualizará el tenant!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {
                        clearValidationErrors('msgError');

                        Swal.fire({
                            title: 'Actualizando empresa...',
                            html: 'Por favor, espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        const formData = new FormData(formUpdateCompany);
                        formData.append('_method', 'PUT');

                        const res = await axios.post(formUpdateCompany.action, formData);
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            redirect("landlord.mantenimientos.empresa");
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                            Swal.close();
                        }
                    } catch (error) {
                        Swal.close();
                        if (error.response && error.response.status === 422) {
                            const errors = error.response.data.errors;
                            paintValidationErrors(errors, 'error');
                            toastr.error('ERRORES DE VALIDACIÓN EN EL FORMULARIO');
                            return;
                        } else {
                            toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR EMPRESA');
                        }
                    }
                }
            });
        }

        function loadTomSelect() {
            const departmentSelect = document.getElementById('department');
            if (departmentSelect && !departmentSelect.tomselect) {
                window.departmentSelect = new TomSelect(departmentSelect, {
                    valueField: 'id',
                    labelField: 'description',
                    searchField: ['description', 'id'],
                    create: false,
                    sortField: { field: 'id', direction: 'asc' },
                    plugins: ['clear_button'],
                    render: {
                        option: (item, escape) => `<div>${escape(item.description)}</div>`,
                        item: (item, escape) => `<div>${escape(item.description)}</div>`
                    }
                });
            }

            const provinceSelect = document.getElementById('province');
            if (provinceSelect && !provinceSelect.tomselect) {
                window.provinceSelect = new TomSelect(provinceSelect, {
                    valueField: 'id',
                    labelField: 'description',
                    searchField: ['description', 'id'],
                    create: false,
                    sortField: { field: 'id', direction: 'asc' },
                    plugins: ['clear_button'],
                    render: {
                        option: (item, escape) => `<div>${escape(item.description)}</div>`,
                        item: (item, escape) => `<div>${escape(item.description)}</div>`
                    }
                });
            }

            const districtSelect = document.getElementById('district');
            if (districtSelect && !districtSelect.tomselect) {
                window.districtSelect = new TomSelect(districtSelect, {
                    valueField: 'id',
                    labelField: 'description',
                    searchField: ['description', 'id'],
                    create: false,
                    sortField: { field: 'id', direction: 'desc' },
                    plugins: ['clear_button'],
                    render: {
                        option: (item, escape) => `<div>${escape(item.description)}</div>`,
                        item: (item, escape) => `<div>${escape(item.description)}</div>`
                    }
                });
            }
        }

        function changeDepartment(department_id) {
            const lstProvinces = @json($provinces);

            if (department_id) {
                department_id = String(department_id).padStart(2, '0');

                const lstProvincesFiltered = lstProvinces.filter((province) => province.department_id == department_id);

                window.provinceSelect.clearOptions();
                lstProvincesFiltered.forEach(province => {
                    window.provinceSelect.addOption({ id: province.id, description: province.name });
                });
                window.provinceSelect.setValue(null);
            }
        }

        function changeProvince(province_id) {
            const lstDistricts = @json($districts);

            if (province_id) {
                province_id = String(province_id).padStart(4, '0');

                const lstDistrictsFiltered = lstDistricts.filter((district) => district.province_id == province_id);

                window.districtSelect.clearOptions();
                lstDistrictsFiltered.forEach(district => {
                    window.districtSelect.addOption({ id: district.id, description: district.name });
                });
                window.districtSelect.setValue(null);
            }
        }

        function setUbigeoPreview() {
            const departmentSelect = document.getElementById("department");
            if (departmentSelect) {
                departmentSelect.dispatchEvent(new Event("change"));
            }

            window.provinceSelect.setValue("{{ $tenant_data->province_id }}");
            window.districtSelect.setValue("{{ $tenant_data->district_id }}");
        }
    </script>

    <script>
        $('#logo').change(function() {
            let reader = new FileReader();
            let file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                reader.onload = (e) => {
                    $('#show-logo').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            } else {
                alert('Seleccione una imagen');
                $('#show-logo').attr('src', null);
                $(this).val('');
            }
        });
    </script>
@endsection
