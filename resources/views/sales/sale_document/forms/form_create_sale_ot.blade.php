<form id="form-create-quote" method="POST">
    @csrf
    @method('POST')

    <p class="text-muted mb-2">
        <span class="text-danger">*</span> Los campos marcados son obligatorios.
    </p>

    <!-- ==========================
     CARD INFORMATIVO - ORDEN
=========================== -->
    <div class="card border-secondary mb-4 shadow-sm">
        <div class="card-header bg-light fw-bold small py-2">
            <i class="fas fa-file-invoice text-primary me-1"></i>
            INFORMACIÓN DE LA ORDEN DE TRABAJO
        </div>

        <div class="card-body">

            <div class="row g-3 small">

                <!-- Nº Orden -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-hashtag text-secondary me-2"></i>
                        <div>
                            <div class="fw-bold">Orden</div>
                            <div>OT-{{ $work_order->id }}</div>
                        </div>
                    </div>
                </div>

                <!-- Estado -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        <div>
                            <div class="fw-bold">Estado</div>
                            <span class="badge bg-primary">{{ $work_order->status }}</span>
                        </div>
                    </div>
                </div>

                <!-- Fecha -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-calendar-alt text-warning me-2"></i>
                        <div>
                            <div class="fw-bold">Fecha Registro</div>
                            <div>{{ $work_order->created_at->format('d/m/Y H:i') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Almacén -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-warehouse text-dark me-2"></i>
                        <div>
                            <div class="fw-bold">Almacén</div>
                            <div>{{ $work_order->warehouse_name }}</div>
                        </div>
                    </div>
                </div>

                <!-- Cliente -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user text-primary me-2"></i>
                        <div>
                            <div class="fw-bold">Cliente</div>
                            <div>
                                {{ $work_order->customer_name }} <br>
                                <span class="">
                                    {{ $work_order->customer_type_document_abbreviation }}:
                                    {{ $work_order->customer_document_number }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Vehículo -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-car text-secondary me-2"></i>
                        <div>
                            <div class="fw-bold">Vehículo</div>
                            <div>
                                {{ $work_order->vehicle->plate ?? '-' }}
                                <span class="">
                                    {{ $work_order->vehicle->brand->description ?? '' }}
                                    {{ $work_order->vehicle->model->description ?? '' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total -->
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-coins text-success me-2"></i>
                        <div>
                            <div class="fw-bold">Total</div>
                            <div>S/ {{ number_format($work_order->total, 2) }}</div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
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

                <!-- Comprobante -->
                <div class="col-lg-6 col-md-8 col-sm-12">
                    <label class="form-label fw-bold required_field">Comprobante:</label>

                    <select class="form-control" id="invoice_type" name="invoice_type" required>
                        <option value="">Seleccione un comprobante</option>
                        @foreach ($invoice_types as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                    <p class="invoice_type_error msgError mb-0"></p>
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

                <!-- TABLA -->
                <div class="col-12 mt-3">
                    <div class="table-responsive">
                        @include('workshop.work_orders.tables.tbl_list_orders_products')
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

                <div class="col-lg-2 col-md-4 col-sm-6 col-xs-12">
                </div>

                <div class="col-12">
                    <div class="table-responsive">
                        @include('workshop.work_orders.tables.tbl_list_orders_services')
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
