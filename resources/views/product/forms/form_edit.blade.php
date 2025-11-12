<form class="row" id="form-edit-product" action="">
    @csrf
    <div class="col-lg-6 col-md-6">
        <div class="row">
            <!-- Nombre -->
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="name_edit" class="form-label required_field">Nombre</label>
                <input placeholder="Nombre máximo 160 caracteres" name="name_edit" required maxlength="160" type="text"
                    class="form-control name_edit" id="name_edit" oninput="this.value = this.value.toUpperCase()">
                <p class="msgError name_edit_error"></p>
            </div>

            <!-- Descripción -->
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="description_edit" class="form-label">Descripción</label>
                <input placeholder="Máximo 200 caracteres" name="description_edit" maxlength="200" type="text"
                    class="form-control description" id="description_edit"
                    oninput="this.value = this.value.toUpperCase()">
                <p class="msgError description_edit_error"></p>
            </div>

            <!-- Precio Venta -->
            <div class="col-lg-6 col-md-6 mb-3">
                <label for="sale_price_edit" class="form-label required_field">Precio Venta</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                    <input value="1" name="sale_price_edit" type="number" step="0.01" min="0"
                        class="form-control sale_price_edit" id="sale_price_edit">
                </div>
                <p class="msgError sale_price_edit_error"></p>
            </div>

            <!-- Precio Compra -->
            <div class="col-lg-6 col-md-6 mb-3">
                <label for="purchase_price_edit" class="form-label required_field">Precio Compra</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                    <input value="1" name="purchase_price_edit" type="number" step="0.01" min="0"
                        class="form-control purchase_price_edit" id="purchase_price_edit">
                </div>
                <p class="msgError purchase_price_edit_error"></p>
            </div>

            <!-- Stock mínimo -->
            <div class="col-lg-6 col-md-6 mb-3 colStock_editMin">
                <label for="stock_min_edit" class="form-label required_field">Stock
                    Mínimo</label>
                <input value="0" name="stock_min_edit" type="number" class="form-control stock_min_edit"
                    min="0" id="stock_min_edit" placeholder="STOCK MÍNIMO">
                <p class="msgError stock_min_edit_error"></p>
            </div>

            <!-- Código Fábrica -->
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="code_factory_edit" class="form-label">Código
                    Fábrica</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-industry"></i></span>
                    <input name="code_factory_edit" type="text" class="form-control code_factory_edit"
                        id="code_factory_edit">
                </div>
                <p class="msgError code_factory_edit_error"></p>
            </div>

            <!-- Código Barras -->
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="code_bar_edit" class="form-label">Código Barras</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                    <input name="code_bar_edit" type="text" class="form-control code_bar_edit" id="code_bar_edit">
                </div>
                <p class="msgError code_bar_edit_error"></p>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6">
        <div class="row">
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="category_id_edit" class="form-label required_field">Categoría</label>

                <select name="category_id_edit" style="text-transform: uppercase;" id="category_id_edit"
                    class="form-select select2_form_product_edit" aria-label="Default select example">
                    <option value="0" selected>SELECCIONAR CATEGORÍA</option>
                    @foreach ($categories as $category)
                        <option style="text-transform: uppercase;" value="{{ $category->id }}">
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                {{-- <a onclick="openCreatecategory_id_editModal()" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i>
                    </a> --}}
                <p class="msgError category_id_edit_error"></p>
            </div>
            <div class="col-lg-12 col-md-12 mb-3">

                <label for="brand_id_edit" class="form-label required_field">Marca</label>
                <select name="brand_id_edit" style="text-transform: uppercase;" id="brand_id_edit"
                    class="form-select select2_form_product_edit" aria-label="Default select example">
                    <option value="0" selected>SELECCIONAR MARCA</option>
                    @foreach ($brands as $brand)
                        <option style="text-transform: uppercase;" value="{{ $brand->id }}">{{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                {{-- <a onclick="openCreateBrandModal()" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i>
                </a> --}}
                <p class="msgError brand_id_edit_error"></p>
            </div>

            <div class="col-lg-12 col-md-12 mb-3">
                <div class="form-group">
                    <label for="image_edit" class="font-weight-bold" style="font-weight: bold;">IMAGEN</label>
                    <div class="d-flex align-items-center mb-2">
                        <input id="image_edit" name="image_edit" class="form-control form-control-sm mr-2"
                            type="file" accept="image/*">
                        <button type="button" class="btn btn-danger btnSetImgEditDefault" title="Quitar imagen">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                    <span class="image_edit_error msgError text-danger"></span>
                    <div id="img_preview_container"
                        class="border rounded d-flex align-items-center justify-content-center"
                        style="height:160px; width:100%; border: 2px dashed #ddd; padding: 10px; text-align: center;">
                        <img class="imgShowLightBox" src="{{ asset('assets/img/products/img_default.png') }}"
                            id="img_vista_previa_edit"
                            style="height: 160px; max-width:260px; object-fit: cover; cursor:pointer;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
