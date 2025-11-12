@extends('layouts.template')

@section('title')
    Editar Empresa
@endsection

@section('css')
@endsection

@section('content')
    <div class="row"> 
        <form action="{{ route('landlord.mantenimientos.empresas.update',['id'=>$company->id]) }}" method="POST" enctype="multipart/form-data">
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
                                        value="{{$company->domain}}"
                                        readonly>
                                    <span class="input-group-text">.eldeportivo.online</span>
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
                                                value="{{ $company->ruc }}">
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
                                        id="razon_social" name="razon_social" value="{{ $company->business_name }}">
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
                                            value="{{ $company->abbreviated_business_name }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="ubigeo">Ubigeo:</label>
                                        <input type="text" class="form-control" id="ubigeo" name="ubigeo" value="{{$company->zip_code}}">
                                    </div>
                                </div>
                                @error('razon_social_abreviada')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror

                                <div class="mb-3">
                                    <label for="direccion_fiscal" class="form-label">Dirección Fiscal</label>
                                    <textarea class="form-control" id="direccion_fiscal" name="direccion_fiscal" rows="2">{{$company->fiscal_address}}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="correo">Correo:</label>
                                            <input type="email" class="form-control" id="correo" name="correo"
                                                value="{{$user->email}}">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <div class="form-password-toggle">
                                                <label class="form-label" for="password">Password</label>
                                                <div class="input-group input-group-merge">
                                                    <input type="password" class="form-control" id="password"
                                                        name="password" value="{{$user->password_visible}}">
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
                                                id="secondary_user" name="secondary_user">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-password-toggle">
                                            <label class="form-label" for="secondary_user">Clave de Usuario
                                                secundario:</label>
                                            <div class="input-group">
                                                <span class="input-group-text"><i class='bx bx-shield'></i></span>
                                                <input type="password" class="form-control" id="secondary_password"
                                                    name="secondary_password">
                                                <span class="input-group-text cursor-pointer">
                                                    <i class="bx bx-hide"></i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="certificate_url">Certificado:</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="certificate_url"
                                            name="certificate_url">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="certificate_password">Contraseña de
                                        Certificado:</label>
                                    <div class="input-group">
                                        <textarea class="form-control" id="certificate_password" name="certificate_password" rows="5"></textarea>
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
                                                @if ($plan->id == $company->plan)
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
                        '<div class="spinner-border spinner-border-sm text-white" role="status"></div> Guardando...'
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
@endsection
