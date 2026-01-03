<form id="form-empresa-store" action="{{ route('landlord.mantenimientos.empresas.store') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
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
                    data-bs-target="#navs-pills-top-banco" aria-controls="navs-pills-top-banco" aria-selected="false">
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
                                value="{{ old('domain') }}">
                            <span class="input-group-text">.tallersuite.store</span>
                            <br>
                        </div>
                        <p class="domain_error msgError mb-0"></p>
                        <div class="row mb-3">
                            <div class="col-6">
                                <label class="form-label" for="ruc">RUC:</label>
                                <div class="input-group">
                                    <input type="text" class="form-control @error('ruc') is-invalid @enderror"
                                        id="ruc" name="ruc" placeholder="Número de ruc"
                                        value="{{ old('ruc') }}">
                                    <button class="btn btn-outline-primary" type="button" id="btn_consulta_sunat"
                                        style="padding-right: 10px; padding-left: 10px;"><i class="bx bx-search"></i>
                                        Sunat</button>
                                </div>
                                <p class="ruc_error msgError mb-0"></p>
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
                                id="razon_social" name="razon_social" value="{{ old('razon_social') }}">
                        </div>
                        <p class="razon_social_error msgError mb-0"></p>
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
                                <p class="razon_social_abreviada_error msgError mb-0"></p>
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
                                    <input type="email" class="form-control" id="correo" name="correo"
                                        value="admin@gmail.com">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="mb-3">
                                    <div class="form-password-toggle">
                                        <label class="form-label" for="password">Password</label>
                                        <div class="input-group input-group-merge">
                                            <input type="password" class="form-control" id="password"
                                                name="password" value="taller123">

                                            <span class="input-group-text cursor-pointer" id="togglePassword">
                                                <i class="fas fa-eye"></i>
                                            </span>
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
                                    <span class="input-group-text"><i class='fas fa-user'></i></span>
                                    <input type="text" class="form-control" placeholder="Usuario secundario"
                                        id="secondary_user" name="secondary_user">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-password-toggle">
                                    <label class="form-label" for="secondary_user">Clave de Usuario
                                        secundario:</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class='fas fa-shield'></i></span>
                                        <input type="password" class="form-control" id="secondary_password"
                                            name="secondary_password">
                                        <span class="input-group-text cursor-pointer">
                                            <i class="fas fa-hide"></i></span>
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
                                <button type="button" class="btn btn-outline-primary me-1" data-bs-toggle="modal"
                                    data-bs-target="#modal_create_plan">
                                    Nuevo Plan
                                </button>
                            </label>
                            <div class="input-group">
                                <select name="plan_id" id="plan_id"
                                    class="@error('plan_id') is-invalid @enderror form-select">
                                    <option value="">Seleccione ...</option>
                                    @foreach ($plans as $plan)
                                        <option value="{{ $plan->id }}">{{ $plan->description }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @error('plan_id')
                            <p style="color: red; margin-top: -10px;">* {{ $message }}</p>
                        @enderror
                        <p class="plan_id_error msgError mb-0"></p>

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
                                                value="{{ $module->id }}" checked>
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
                                                    name="child_id[]" value="{{ $child->id }}" checked>
                                                <label class="form-check-label" for="children{{ $child->id }}">
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
                                                        name="grandchild_id[]" value="{{ $grandchild->id }}" checked>
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
