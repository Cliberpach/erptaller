<div class="row g-3 mb-3 pt-3">

    <!-- PRODUCTO -->
    <div class="col-lg-6 col-md-8 col-sm-12">
        <label class="form-label fw-bold">Producto:</label>
        <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateProduct();" style="margin-left:4px;"></i>
        <select class="form-control" id="product_id" name="product_id">
            <option value="">Seleccionar</option>
        </select>
        <p class="product_id_error msgError mb-0"></p>
    </div>

    <!-- CANTIDAD -->
    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
        <label class="form-label fw-bold">Cantidad:</label>
        <input type="text" class="form-control inputDecimalPositivo" id="product_quantity" name="product_quantity"
            placeholder="0.00">
        <p class="product_quantity_error msgError mb-0"></p>
    </div>

    <!-- PRECIO -->
    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
        <label class="form-label fw-bold">Precio:</label>
        {{-- Precio editable segun flag VEP (mismo gate que el modal). Autocompletado por JS al
             seleccionar producto; readonly solo bloquea al usuario (el JS igual setea .value). --}}
        <input @readonly(! \App\Support\PrecioVenta::puedeEditar()) type="text" class="form-control inputDecimalPositivo" id="product_price" name="product_price"
            placeholder="0.00">
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
