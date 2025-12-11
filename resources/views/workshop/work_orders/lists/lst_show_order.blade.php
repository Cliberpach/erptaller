<!-- Sección Maestro -->
<div class="mb-4">
    <h6 class="text-primary"><i class="fa fa-info-circle me-1"></i> Información General</h6>
    <div class="row">
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Almacén:</strong> <span id="show_warehouse_name"></span>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Cliente:</strong> <span id="show_customer_name"></span>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Tipo Doc.:</strong> <span
                id="show_customer_type_document"></span>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Número Doc.:</strong> <span
                id="show_customer_document_number"></span></div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Placa:</strong> <span id="show_vehicle_plate"></span>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Total:</strong> S/ <span id="show_total"></span>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Subtotal:</strong> S/ <span id="show_subtotal"></span>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>IGV:</strong> S/ <span id="show_igv"></span></div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Estado:</strong> <span
                id="show_status"class="badge bg-primary"></span></div>
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12"><strong>Validación Stock:</strong>
            <span id="show_validation_stock" class="badge bg-primary"></span>
        </div>
    </div>
</div>

<hr>

<!-- Sección Productos -->
<div class="mb-4">
    <h6 class="text-primary"><i class="fa fa-boxes me-1"></i> Productos</h6>
    <div class="table-responsive">
        <table class="table-sm table-bordered table">
            <thead class="table-light">
                <tr>
                    <th>Producto</th>
                    <th>Unidad</th>
                    <th>Categoría</th>
                    <th>Marca</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody id="show_products_body">
                <!-- Se llenará dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<hr>

<!-- Sección Servicios -->
<div class="mb-4">
    <h6 class="text-primary"><i class="fa fa-concierge-bell me-1"></i> Servicios</h6>
    <div class="table-responsive">
        <table class="table-sm table-bordered table">
            <thead class="table-light">
                <tr>
                    <th>Servicio</th>
                    <th>Cantidad</th>
                    <th>Precio</th>
                    <th>Importe</th>
                </tr>
            </thead>
            <tbody id="show_services_body">
                <!-- Se llenará dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<hr>

<!-- Sección Técnicos -->
<div class="mb-4">
    <h6 class="text-primary"><i class="fa fa-user-cog me-1"></i> Técnicos</h6>
    <div class="table-responsive">
        <table class="table-sm table-bordered table">
            <thead class="table-light">
                <tr>
                    <th>ID Técnico</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody id="show_technicians_body">
                <!-- Se llenará dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<hr>

<!-- Sección Inventario -->
<div class="mb-4">
    <h6 class="text-primary"><i class="fa fa-clipboard-list me-1"></i> Inventario</h6>
    <div class="table-responsive">
        <table class="table-sm table-bordered table">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                </tr>
            </thead>
            <tbody id="show_inventory_body">
                <!-- Se llenará dinámicamente -->
            </tbody>
        </table>
    </div>
</div>

<!-- Sección Imágenes -->
<div class="mb-4">
    <h6 class="text-primary"><i class="fa fa-image me-1"></i> Imágenes</h6>
    <div class="row" id="show_images_body">
        <!-- Se llenará dinámicamente -->
    </div>
</div>
