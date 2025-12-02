<form id="form-create-quote" method="POST">
    @csrf
    @method('POST')

    <p class="text-muted mb-2">
        <span class="text-danger">*</span> Los campos marcados son obligatorios.
    </p>

    <!-- ==========================
         SECCIÓN PRINCIPAL / CLIENTE
    =========================== -->
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
                            <option @if ($warehouse->id == 1) selected @endif value="{{ $warehouse->id }}">
                                {{ $warehouse->descripcion }}</option>
                        @endforeach
                    </select>
                    <p class="warehouse_id_error msgError mb-0"></p>
                </div>

                <!-- Cliente -->
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <label class="form-label fw-bold required_field">Cliente:</label>
                    <i class="fas fa-user-plus btn btn-warning btn-sm" onclick="openMdlNewCustomer();"
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
                        maxlength="8" minlength="6" placeholder="Ej: ABC123" >
                    <p class="plate_error msgError mb-0"></p>
                </div>

                <!-- Fecha Expiración -->
                <div class="col-lg-6 col-md-4 col-sm-6 col-xs-12">
                    <label class="form-label fw-bold">Fecha Expiración cotización:</label>
                    <input type="date" class="form-control" id="expiration_date" name="expiration_date">
                    <p class="expiration_date_error msgError mb-0"></p>
                </div>

            </div>

        </div>
    </div>

    <!-- ==========================
         SECCIÓN PRODUCTOS
    =========================== -->
    <div class="card border-success mb-4 shadow-sm">
        <div class="card-header bg-primary fw-bold py-2 text-white">
            Productos
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3 pt-3">

                <!-- PRODUCTO -->
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <label class="form-label fw-bold">Producto:</label>
                    <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateProduct();"
                        style="margin-left:4px;"></i>
                    <select class="form-control" id="product_id" name="product_id">
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="product_id_error msgError mb-0"></p>
                </div>

                <!-- CANTIDAD -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <label class="form-label fw-bold">Cantidad:</label>
                    <input type="text" class="form-control inputDecimalPositivo" id="product_quantity"
                        name="product_quantity" placeholder="0.00">
                    <p class="product_quantity_error msgError mb-0"></p>
                </div>

                <!-- PRECIO -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <label class="form-label fw-bold">Precio:</label>
                    <input type="text" class="form-control inputDecimalPositivo" id="product_price"
                        name="product_price" placeholder="0.00">
                    <p class="product_price_error msgError mb-0"></p>
                </div>

                <!-- AGREGAR -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <button class="btn btn-primary btn-add-product" type="button">AGREGAR <i class="fas fa-plus"
                            style="margin-left:4px;"></i></button>
                </div>

                <!-- TABLA -->
                <div class="col-12 mt-3">
                    <div class="table-responsive">
                        @include('workshop.quotes.tables.tbl_list_quotes_products')
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ==========================
         SECCIÓN SERVICIOS
    =========================== -->
    <div class="card border-info mb-4 shadow-sm">
        <div class="card-header bg-info fw-bold py-2 text-white">
            Servicios
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3 pt-3">

                <div class="col-lg-6 col-md-8 col-sm-12">
                    <label class="form-label fw-bold">Servicio:</label>
                     <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateService();"
                        style="margin-left:4px;"></i>
                    <select class="form-control" id="service_id" name="service_id">
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="service_id_error msgError mb-0"></p>
                </div>

                <!-- CANTIDAD -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <label class="form-label fw-bold">Cantidad:</label>
                    <input type="text" class="form-control inputDecimalPositivo" id="service_quantity"
                        name="service_quantity" placeholder="0.00">
                    <p class="service_quantity_error msgError mb-0"></p>
                </div>

                <!-- PRECIO -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <label class="form-label fw-bold">Precio:</label>
                    <input type="text" class="form-control inputDecimalPositivo" id="service_price"
                        name="service_price" placeholder="0.00">
                    <p class="service_price_error msgError mb-0"></p>
                </div>

                <!-- AGREGAR -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <button class="btn btn-primary btn-add-service" type="button">AGREGAR <i class="fas fa-plus"
                            style="margin-left:4px;"></i></button>
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        @include('workshop.quotes.tables.tbl_list_quotes_services')
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
