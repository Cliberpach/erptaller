@extends('layouts.template')

@section('title')
    Empresa
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <form action="{{ route('landlord.mantenimientos.empresas.store') }}" method="POST">
            @csrf
            <div class="nav-align-top mb-4">
                <ul class="nav nav-pills mb-3" role="tablist">b
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
                                        placeholder="Nombre del dominio" name="domain" value="{{ old('domain') }}">
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
                                                value="{{ old('ruc') }}">
                                            <button class="btn btn-outline-primary" type="button" id="btn_consulta_sunat"
                                                style="padding-right: 10px; padding-left: 10px;"><i
                                                    class="bx bx-search"></i> Sunat</button>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <label class="form-label" for="estado">Estado:</label>
                                        <input type="text" class="form-control text-center" id="estado" name="estado"
                                            readonly value="SIN VERTIFICAR">
                                    </div>
                                </div>
                                @error('ruc')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror

                                <div class="mb-3">
                                    <label class="form-label" for="razon_social">Razón social:</label>
                                    <input type="text" class="form-control @error('razon_social') is-invalid @enderror"
                                        id="razon_social" name="razon_social" value="{{ old('razon_social') }}">
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
                                            value="{{ old('razon_social_abreviada') }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="ubigeo">Ubigeo:</label>
                                        <input type="text" class="form-control" id="ubigeo" name="ubigeo">
                                    </div>
                                </div>
                                @error('razon_social_abreviada')
                                    <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                                @enderror

                                <div class="mb-3">
                                    <label for="direccion_fiscal" class="form-label">Dirección Fiscal</label>
                                    <textarea class="form-control" id="direccion_fiscal" name="direccion_fiscal" rows="2"></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="correo">Correo:</label>
                                            <input type="text" class="form-control" id="correo" name="correo"
                                                value="admin@gmail.com">
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="password">Password:</label>
                                            <input type="password" class="form-control" id="correo" name="password"
                                                value="12345678">
                                        </div>
                                    </div>
                                </div>


                                {{-- <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label" for="telefono">Teléfono:</label>
                                        <input type="text" class="form-control" id="telefono" name="telefono">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label" for="celular">Celular:</label>
                                        <input type="text" class="form-control" id="celular" name="celular">
                                    </div>
                                </div> --}}

                            </div>
                            <div class="col-sm-6 col-12">
                                <p>Facturación Electrónica</p>
                                <div class="mb-3">
                                    <label class="form-label" for="secondary_user">Usuario Secundario:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="secondary_user"
                                            name="secondary_user">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="secondary_password">Clave de Usuario:</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="secondary_password"
                                            name="secondary_password">
                                    </div>
                                </div>

                                {{-- <div class="mb-3">
                                    <label class="form-label" for="logo">Logo:</label>
                                    <div class="input-group">
                                        <input type="file" class="form-control" id="logo" name="logo">
                                    </div>

                                    <div class="form-group row mt-4 text-center">
                                        <p>
                                            <img class="logo" id="show-logo"
                                                src="{{ asset('assets/img/avatars/1.png') }}" alt="logo-empresa"
                                                accept="image/*" width="200px" height="200px"
                                                style="border-radius: 10%;">
                                        </p>
                                    </div>
                                </div>
                                <div class="divider">
                                    <div class="divider-text">Redes Sociales</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label" for="facebook">Facebook:</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname1" class="input-group-text"><i
                                                class="bx bxl-facebook"></i></span>
                                        <input type="text" class="form-control" id="facebook" name="facebook"
                                            aria-label="Facebook" aria-describedby="basic-icon-default-fullname2">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="instagram">Instagram:</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname2" class="input-group-text"><i
                                                class="bx bxl-instagram"></i></span>
                                        <input type="text" class="form-control" id="instagram" name="instagram"
                                            aria-label="Intragram" aria-describedby="basic-icon-default-fullname2">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="web">Web:</label>
                                    <div class="input-group input-group-merge">
                                        <span id="basic-icon-default-fullname3" class="input-group-text"><i
                                                class="bx bx-world"></i></span>
                                        <input type="text" class="form-control" id="web" name="web"
                                            aria-label="Web" aria-describedby="basic-icon-default-fullname2">
                                    </div>
                                </div> --}}
                            </div>
                        </div>
                        <div class="tab-pane fade" id="navs-pills-top-banco" role="tabpanel">
                            {{-- modulo --}}
                            <p>Módulos</p>

                            <div class="row">
                                @foreach ($modules as $module)
                                    <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                                        <div class="card h-100 mb-4">
                                            <div class="card-body">
                                                <div class="card-text mb-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input module-checkbox" type="checkbox"
                                                            id="module{{ $module->id }}" name="module_id[]"
                                                            value="{{ $module->id }}">
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
                                                                name="{{ $child->grandchildren->isNotEmpty() ? 'child_grandchild_id[]' : 'child_id[]' }}"
                                                                value="{{ $child->id }}">
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
                                                                    type="checkbox"
                                                                    id="grandchildren{{ $grandchild->id }}"
                                                                    name="grandchild_id[]" value="{{ $grandchild->id }}">
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
@endsection

@section('css')
@endsection

@section('js')
    <script>
        $(document).on('click', '#btn_consulta_sunat', function() {
            const user_ruc = $('#ruc').val();
            $.get('/landlord/ruc/' + user_ruc, function(data) {
                $('#estado').val(data.data.estado);
                $('#razon_social').val(data.data.nombre_o_razon_social);
                $('#razon_social_abreviada').val(data.data.nombre_o_razon_social);
            });
        });

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
@endsection
