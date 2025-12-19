<form id="form-create-quote" method="POST">
    @csrf
    @method('POST')

    <p class="text-muted mb-2">
        <span class="text-danger">*</span> Los campos marcados son obligatorios.
    </p>

    <!-- ==========================
         SECCIÓN PRINCIPAL / CLIENTE
    =========================== -->

    <div class="row">
        <div class="col-12">
            <div class="card border-primary mb-4 shadow-sm">
                <div class="card-header bg-primary fw-bold py-2 text-white">
                    Datos del Cliente y Vehículo
                </div>

                <div class="card-body">

                    <div class="row g-3 pt-3">

                        <!-- Almacén -->
                        <div class="col-12 d-none">
                            <label class="form-label fw-bold required_field">Almacén:</label>
                            <select class="form-control" id="warehouse_id" name="warehouse_id" required>
                                <option value="">Seleccionar</option>
                                @foreach ($warehouses as $warehouse)
                                    <option @if ($warehouse->id == 1) selected @endif
                                        value="{{ $warehouse->id }}">
                                        {{ $warehouse->descripcion }}</option>
                                @endforeach
                            </select>
                            <p class="warehouse_id_error msgError mb-0"></p>
                        </div>

                        <!-- Cliente -->
                        <div class="col-lg-6 col-md-8 col-sm-12">
                            <label class="form-label fw-bold required_field">Cliente:</label>
                            <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlNewCustomer();"
                                style="margin-left:4px;"></i>

                            <select class="form-control" id="client_id" name="client_id" required>
                                <option value="">Seleccione un cliente</option>
                            </select>
                            <p class="client_id_error msgError mb-0"></p>
                        </div>

                        <!-- Vehículo -->
                        <div class="col-lg-6 col-md-8 col-sm-12">
                            <label class="form-label fw-bold">Vehículo:</label>
                            <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateVehicle();"
                                style="margin-left:4px;"></i>

                            <select class="form-control" id="vehicle_id" name="vehicle_id">
                                <option value="">Seleccionar</option>
                            </select>
                            <p class="vehicle_id_error msgError mb-0"></p>
                        </div>

                        <!-- Placa -->
                        <div class="col-lg-6 col-md-6 col-sm-12">
                            <label class="form-label fw-bold">Placa:</label>
                            <input type="text" class="form-control text-uppercase" id="plate" name="plate"
                                maxlength="8" minlength="6" placeholder="Ej: ABC123">

                            <div id="vehicle_info" class="d-none bg-light mt-2 rounded border p-2">
                                <i class="fas fa-car text-primary me-1"></i>
                                <span class="fw-semibold"></span>
                                <span class="text-muted"></span>
                            </div>

                            <p class="plate_error msgError mb-0"></p>
                        </div>

                        <!-- Fecha Expiración -->
                        <div class="col-lg-6 col-md-4 col-sm-6 col-xs-12">
                            <label class="form-label fw-bold">Fecha Expiración cotización:</label>
                            <input type="date" class="form-control" id="expiration_date" name="expiration_date"
                                value="<?= date('Y-m-d', strtotime('+10 days')) ?>">
                            <p class="expiration_date_error msgError mb-0"></p>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-12">
            <h6 class="card-title mb-3">Detalles</h6>
            <div class="clearfix">
                <div class="card">
                    <div class="card-header">
                        <ul class="nav nav-pills card-header-pills" id="myTabPills" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active d-flex align-items-center gap-2" id="products-tabPills"
                                    data-bs-toggle="tab" data-bs-target="#productPills" type="button" role="tab"
                                    aria-controls="productPills" aria-selected="true">Productos
                                    <span class="badge bg-danger rounded-pill badge-products d-none">
                                        0
                                    </span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center gap-2" id="services-tabPills" data-bs-toggle="tab"
                                    data-bs-target="#servicePills" type="button" role="tab"
                                    aria-controls="servicePills" aria-selected="false">Servicios
                                    <span class="badge bg-danger rounded-pill badge-services d-none">
                                        0
                                    </span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="productPills" role="tabpanel"
                                aria-labelledby="products-tabPills" tabindex="0">

                                @include('workshop.quotes.tabs.tab_products')

                            </div>
                            <div class="tab-pane fade" id="servicePills" role="tabpanel"
                                aria-labelledby="services-tabPills" tabindex="0">

                                @include('workshop.quotes.tabs.tab_services')

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 d-flex justify-content-lg-end">
            <div class="col-12 col-lg-4">
                <div class="table-responsive">
                    @include('workshop.quotes.tables.tbl_amounts')
                </div>
            </div>
        </div>
    </div>


</form>
