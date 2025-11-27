<form id="form_edit_product" method="POST">
    @csrf
    @method('POST')

    <!-- Product Info Card -->
    <div class="card mb-4 shadow-sm">
        <div class="card-body small">
            <div class="row g-3">
                <div class="col-12 col-sm-6 col-lg-3">
                    <p class="fw-bold mb-1"><i class="fas fa-box text-primary me-1"></i>Nombre:</p>
                    <span id="product_name_edit" class="text-muted">—</span>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <p class="fw-bold mb-1"><i class="fas fa-layer-group text-success me-1"></i>Categoría:</p>
                    <span id="product_category_edit" class="text-muted">—</span>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <p class="fw-bold mb-1"><i class="fas fa-tags text-warning me-1"></i>Marca:</p>
                    <span id="product_brand_edit" class="text-muted">—</span>
                </div>

                <div class="col-12 col-sm-6 col-lg-3">
                    <p class="fw-bold mb-1"><i class="fas fa-dollar-sign text-danger me-1"></i>Precio Venta Original:</p>
                    <span id="product_original_price_edit" class="text-muted">—</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Inputs de Precio y Cantidad -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <label class="form-label fw-bold"><i class="fas fa-tag text-primary me-1"></i>Nuevo Precio:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-dollar-sign text-success"></i></span>
                <input required type="text" class="form-control inputDecimalPositivo" id="product_price_edit" name="product_price_edit"
                    placeholder="Ingrese nuevo precio">
            </div>
        </div>

        <div class="col-md-6">
            <label class="form-label fw-bold"><i class="fas fa-sort-numeric-up text-warning me-1"></i>Cantidad:</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-cubes text-secondary"></i></span>
                <input required type="text" class="form-control inputDecimalPositivo" id="product_quantity_edit" name="product_quantity_edit"
                    placeholder="Ingrese cantidad">
            </div>
        </div>
    </div>

</form>
