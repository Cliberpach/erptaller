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
            <div class="col-lg-6 col-md-6 colStock mb-3">
                <label for="stock" class="form-label required_field">Stock</label>
                <input value="0" name="stock" type="number" class="form-control stock" min="0"
                    id="stock" aria-describedby="emailHelp" placeholder="STOCK">
                <p class="msgError stock_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 colStockMin mb-3">
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

                <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateCategory();"
                    style="margin-left:4px;"></i>

                <select name="category_id" style="text-transform: uppercase;" id="category_id"
                    class="select2_form form-select" aria-label="Default select example">
                    <option value=""></option>
                    @foreach ($categories as $category)
                        <option @if ($category->is_default) selected @endif style="text-transform: uppercase;"
                            value="{{ $category->id }}">
                            {{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="msgError category_id_error"></p>
            </div>

            <div class="col-lg-12 col-md-12 mb-3">
                <label for="brand" class="form-label required_field">Marca</label>
                <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateBrand();"
                    style="margin-left:4px;"></i>

                <select name="brand_id" style="text-transform: uppercase;" id="brand_id"
                    class="brand select2_form form-select" aria-label="Default select example">
                    <option value=""></option>
                    @foreach ($brands as $brand)
                        <option @if ($brand->is_default) selected @endif style="text-transform: uppercase;"
                            value="{{ $brand->id }}">{{ $brand->name }}
                        </option>
                    @endforeach
                </select>
                <p class="msgError brand_id_error"></p>
            </div>

            <div class="col-lg-12 col-md-12 mb-3">
                <div class="form-group">
                    <label for="image" class="font-weight-bold" style="font-weight: bold;">IMAGEN</label>
                    <div class="d-flex align-items-center mb-2">
                        <input id="image" name="image" class="form-control form-control-sm mr-2"
                            type="file"
                            accept=".jpg,.jpeg,.png,.webp,.avif,image/jpeg,image/png,image/webp,image/avif">
                    </div>
                    <span class="image_error msgError text-danger"></span>
                </div>
            </div>

        </div>
    </div>
</form>
