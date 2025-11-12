<form class="row" id="form-create-product" action="">
    @csrf
    <div class="col-lg-6 col-md-6">
        <div class="row">
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="name" class="form-label required_field">Nombre</label>
                <input placeholder="Nombre máximo 160 caracteres" name="name" required maxlength="160" type="text"
                    class="form-control name" id="name" aria-describedby="emailHelp"
                    oninput="this.value = this.value.toUpperCase()">
                <p class="msgError name_error"></p>
            </div>
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="description" class="form-label">Descripción</label>
                <input placeholder="Máximo 200 caracteres" name="description" maxlength="200" type="text"
                    class="form-control description" id="description" aria-describedby="emailHelp"
                    oninput="this.value = this.value.toUpperCase()">
                <p class="msgError description_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 mb-3">
                <label for="sale_price" class="form-label required_field">Precio venta</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                    <input value="1" name="sale_price" type="number" step="0.01" min="0"
                        class="form-control sale_price" id="sale_price" aria-label="Amount (to the nearest dollar)">
                </div>
                <p class="msgError sale_price_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 mb-3">
                <label for="purchase_price" class="form-label required_field">Precio compra</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                    <input value="1" name="purchase_price" type="number" step="0.01" min="0"
                        class="form-control purchase_price" id="purchase_price"
                        aria-label="Amount (to the nearest dollar">
                </div>
                <p class="msgError purchase_price_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 mb-3 colStock">
                <label for="stock" class="form-label required_field">Stock</label>
                <input value="0" name="stock" type="number" class="form-control stock" min="0"
                    id="stock" aria-describedby="emailHelp" placeholder="STOCK">
                <p class="msgError stock_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 mb-3 colStockMin">
                <label for="stock_min" class="form-label required_field">Stock mínimo</label>
                <input value="0" name="stock_min" type="number" class="form-control stock_min" min="0"
                    id="stock_min" placeholder="STOCK MÍNIMO" aria-label="Username" aria-describedby="basic-addon1">
                <p class="msgError stock_min_error"></p>
            </div>
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="code_factory" class="form-label">Cod Fábrica</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-industry"></i></span>
                    <input name="code_factory" type="number" class="form-control code_factory" id="code_factory"
                        aria-label="Amount (to the nearest dollar)">
                </div>
                <p class="msgError code_factory_error"></p>
            </div>
            <div class="col-lg-12 col-md-12">
                <label for="code_bar" class="form-label">Cod Barras</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-code_bar"></i></span>
                    <input name="code_bar" type="number" class="form-control code_bar" id="code_bar"
                        aria-label="Amount (to the nearest dollar)">
                </div>
                <p class="msgError code_bar_error"></p>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6">
        <div class="row">
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="category_id" class="form-label required_field">Categoría</label>

                <select name="category_id" style="text-transform: uppercase;" id="category_id"
                    class="form-select select2_form" aria-label="Default select example">
                    <option value="0" selected>SELECCIONAR CATEGORÍA</option>
                    @foreach ($categories as $category_id)
                        <option style="text-transform: uppercase;" value="{{ $category_id->id }}">
                            {{ $category_id->name }}</option>
                    @endforeach
                </select>
                {{-- <a onclick="openCreatecategory_idModal()" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i>
                    </a> --}}
                <p class="msgError category_id_error"></p>
            </div>
            <div class="col-lg-12 col-md-12 mb-3">

                <label for="brand" class="form-label required_field">Marca</label>
                <select name="brand_id" style="text-transform: uppercase;" id="brand"
                    class="form-select brand select2_form" aria-label="Default select example">
                    <option value="0" selected>SELECCIONAR MARCA</option>
                    @foreach ($brands as $brand)
                        <option style="text-transform: uppercase;" value="{{ $brand->id }}">{{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                {{-- <a onclick="openCreateBrandModal()" class="btn btn-success">
                        <i class="fas fa-plus-circle"></i>
                    </a> --}}

                <p class="msgError brand_id_error"></p>
            </div>

            <div class="col-lg-12 col-md-12 mb-3">
                <div class="form-group">
                    <label for="image" class="font-weight-bold" style="font-weight: bold;">IMAGEN</label>
                    <div class="d-flex align-items-center mb-2">
                        <input id="image" name="image" class="form-control form-control-sm mr-2"
                            type="file" accept="image/*">
                        <button type="button" class="btn btn-danger btnSetImgDefault" title="Quitar imagen">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                    <span class="image_error msgError text-danger"></span>
                    <div id="img_preview_container"
                        class="border rounded d-flex align-items-center justify-content-center"
                        style="height:160px; width:100%; border: 2px dashed #ddd; padding: 10px; text-align: center;">
                        <img class="imgShowLightBox" src="{{ asset('assets/img/products/img_default.png') }}"
                            id="img_vista_previa"
                            style="height: 160px; max-width:260px; object-fit: cover; cursor:pointer;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
