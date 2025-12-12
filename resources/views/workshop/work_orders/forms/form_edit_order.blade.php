<form id="form-edit-order" method="POST">
    @csrf
    @method('POST')

    <p class="text-muted mb-2">
        <span class="text-danger">*</span> Los campos marcados son obligatorios.
    </p>

    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <strong class="text-dark">Validación de Stock:</strong>

        @if ($configuration->property == 1)
            <span class="text-success fw-bold">ACTIVADA</span>
        @else
            <span class="text-danger fw-bold">DESACTIVADA</span>
        @endif

        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>

    <!-- ==========================
         SECCIÓN PRINCIPAL / CLIENTE
    =========================== -->
    <div class="card border-primary mb-4 shadow-sm">
        <div class="card-header bg-primary fw-bold small py-2 text-white">
            DATOS DEL CLIENTE Y VEHÍCULO
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
                    <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlNewVehicle();"
                        style="margin-left:4px;"></i>

                    <select class="form-control" id="vehicle_id" name="vehicle_id">
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="vehicle_id_error msgError mb-0"></p>
                </div>

            </div>

        </div>
    </div>

    <!-- ==========================
         SECCIÓN PRODUCTOS
    =========================== -->
    <div class="card border-success mb-4 shadow-sm">
        <div class="card-header bg-primary fw-bold small py-2 text-white">
            PRODUCTOS
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3 pt-3">

                <!-- PRODUCTO -->
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <label class="form-label fw-bold">Producto:</label><i class="fas fa-plus btn btn-warning btn-sm"
                        onclick="openMdlNewVehicle();" style="margin-left:4px;"></i>
                    <select class="form-control" id="product_id" name="product_id">
                        <option value="">Seleccionar</option>
                    </select>
                    <p class="product_id_error msgError mb-0"></p>
                </div>

                <!-- STOCK -->
                <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
                    <label class="form-label fw-bold">Stock:</label>
                    <input readonly type="text" class="form-control inputDecimalPositivo" id="product_stock"
                        name="product_stock" placeholder="0.00">
                    <p class="product_stock_error msgError mb-0"></p>
                </div>

                <!-- CANTIDAD -->
                <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
                    <label class="form-label fw-bold">Cantidad:</label>
                    <input type="text" class="form-control inputDecimalPositivo" id="product_quantity"
                        name="product_quantity" placeholder="0.00">
                    <p class="product_quantity_error msgError mb-0"></p>
                </div>

                <!-- PRECIO -->
                <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
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
        <div class="card-header bg-info fw-bold small py-2 text-white">
            SERVICIOS
        </div>

        <div class="card-body">
            <div class="row g-3 mb-3 pt-3">

                <div class="col-lg-6 col-md-8 col-sm-12">
                    <label class="form-label fw-bold">Servicio:</label><i class="fas fa-plus btn btn-warning btn-sm"
                        onclick="openMdlNewVehicle();" style="margin-left:4px;"></i>
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

    <!-- ==========================
     INVENTARIO DEL VEHÍCULO
=========================== -->
    <div class="card border-primary mb-4 shadow-sm">
        <div
            class="card-header bg-primary fw-bold small d-flex justify-content-between align-items-center py-2 text-white">
            INVENTARIO DEL VEHÍCULO
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse"
                data-bs-target="#inventoryCollapse" aria-expanded="true" aria-controls="inventoryCollapse">
                <i class="fas fa-caret-down"></i>
            </button>
        </div>

        <div class="card-body pb-1 pt-3">

            <!-- Contenido colapsable -->
            <div id="inventoryCollapse" class="collapse">
                @foreach ($checks_inventory_vehicle as $category)
                    <h6 class="fw-bold text-secondary mb-2 mt-3">
                        {{ $category['category_name'] }}
                    </h6>

                    <div class="row g-2">
                        @foreach ($category['items'] as $item)
                            <div class="col-lg-3 col-md-4 col-sm-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="inventory_items[]"
                                        value="{{ $item['id'] }}" id="inv_item_{{ $item['id'] }}">

                                    <label class="form-check-label small" for="inv_item_{{ $item['id'] }}">
                                        {{ $item['name'] }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach

                <div class="row g-2 mt-2">
                    <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
                        <label for="fuelSelect" class="form-check-label text-secondary fw-bold">NIVEL DE
                            GASOLINA:</label>
                        <select id="fuelSelect" class="form-control" name="fuel_level">
                            <option value="0">VACÍO</option>
                            <option value="25">1/4</option>
                            <option value="50">1/2</option>
                            <option value="75">3/4</option>
                            <option value="100">LLENO</option>
                        </select>

                        <div style="width: 250px; margin-bottom: 20px;" class="mt-2">
                            <div id="gauge"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="card border-primary mb-4 shadow-sm">
        <div
            class="card-header bg-primary fw-bold small d-flex justify-content-between align-items-center py-2 text-white">
            OPERARIOS
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse"
                data-bs-target="#operariosCollapse" aria-expanded="true" aria-controls="operariosCollapse">
                <i class="fas fa-caret-down"></i>
            </button>
        </div>

        <div class="card-body pb-1 pt-3">
            <div id="operariosCollapse" class="collapse">

                <h6 class="fw-bold text-secondary mb-3 mt-2">
                    Seleccionar Técnicos (Máx: 3)
                </h6>

                <div class="row g-3">

                    <div class="col-lg-4 col-md-6 col-sm-12">
                        <label class="form-label small fw-bold">Técnico</label>
                        <select id="technicians" name="technicians[]" class="select2-tecnicos form-select">
                            <option value="">Seleccionar</option>

                            @foreach ($technicians as $tec)
                                <option value="{{ $tec->id }}">{{ $tec->name }}</option>
                            @endforeach

                        </select>
                    </div>

                </div>

            </div>
        </div>
    </div>


    <div class="card border-primary mb-4 shadow-sm">
        <div
            class="card-header bg-primary fw-bold small d-flex justify-content-between align-items-center py-2 text-white">
            IMÁGENES DEL VEHÍCULO
            <button class="btn btn-light btn-sm" type="button" data-bs-toggle="collapse"
                data-bs-target="#vehicleImagesCollapse" aria-expanded="true" aria-controls="vehicleImagesCollapse">
                <i class="fas fa-caret-down"></i>
            </button>
        </div>

        <div class="card-body pb-1 pt-3">
            <div id="vehicleImagesCollapse" class="collapse">

                <h6 class="fw-bold text-secondary mb-3 mt-2">
                    Subir imágenes del vehículo (Máx: 5)
                </h6>

                <div class="row g-3">

                    @for ($i = 1; $i <= 5; $i++)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                            <label class="form-label small fw-bold">Imagen {{ $i }}</label>
                            <input accept="image/jpeg" data-max-files="1" data-allow-reorder="true"
                                data-max-file-size="3MB" type="file" name="vehicle_images[]" accept="image/*"
                                class="filepond-input">
                        </div>
                    @endfor

                </div>

            </div>
        </div>

    </div>

    <style>
        .filepond--credits {
            display: none !important;
        }
    </style>

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
