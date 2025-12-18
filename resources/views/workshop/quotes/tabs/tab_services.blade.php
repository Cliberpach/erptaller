<div class="row g-3 mb-3 pt-3">

    <div class="col-lg-6 col-md-8 col-sm-12">
        <label class="form-label fw-bold">Servicio:</label>
        <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateService();" style="margin-left:4px;"></i>
        <select class="form-control" id="service_id" name="service_id">
            <option value="">Seleccionar</option>
        </select>
        <p class="service_id_error msgError mb-0"></p>
    </div>

    <!-- CANTIDAD -->
    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
        <label class="form-label fw-bold">Cantidad:</label>
        <input type="text" class="form-control inputDecimalPositivo" id="service_quantity" name="service_quantity"
            placeholder="0.00">
        <p class="service_quantity_error msgError mb-0"></p>
    </div>

    <!-- PRECIO -->
    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
        <label class="form-label fw-bold">Precio:</label>
        <input type="text" class="form-control inputDecimalPositivo" id="service_price" name="service_price"
            placeholder="0.00">
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
