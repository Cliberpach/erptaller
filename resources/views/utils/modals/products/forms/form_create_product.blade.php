<form class="row" id="form_create_product" action="">
    @csrf
    <div class="col-lg-6 col-md-6">
        <div class="row">
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="name_mdlproduct" class="form-label required_field">Nombre</label>
                <input placeholder="Nombre máximo 160 caracteres" name="name_mdlproduct" required maxlength="160"
                    type="text" class="form-control name" id="name_mdlproduct" aria-describedby="emailHelp"
                    oninput="this.value = this.value.toUpperCase()">
                <p class="msgError name_mdlproduct_error"></p>
            </div>
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="description_mdlproduct" class="form-label">Descripción</label>
                <input placeholder="Máximo 200 caracteres" name="description_mdlproduct" maxlength="200" type="text"
                    class="form-control description_mdlproduct" id="description_mdlproduct" aria-describedby="emailHelp"
                    oninput="this.value = this.value.toUpperCase()">
                <p class="msgError description_mdlproduct_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 mb-3">
                <label for="sale_price_mdlproduct" class="form-label required_field">Precio venta</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                    <input value="1" name="sale_price_mdlproduct" type="number" step="0.01" min="0"
                        class="form-control sale_price_mdlproduct" id="sale_price_mdlproduct"
                        aria-label="Amount (to the nearest dollar)">
                </div>
                <p class="msgError sale_price_mdlproduct_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 mb-3">
                <label for="purchase_price_mdlproduct" class="form-label required_field">Precio compra</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-coins"></i></span>
                    <input value="1" name="purchase_price_mdlproduct" type="number" step="0.01" min="0"
                        class="form-control purchase_price_mdlproduct" id="purchase_price_mdlproduct"
                        aria-label="Amount (to the nearest dollar">
                </div>
                <p class="msgError purchase_price_mdlproduct_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 colStock mb-3">
                <label for="stock_mdlproduct" class="form-label required_field">Stock</label>
                <input value="0" name="stock_mdlproduct" type="number" class="form-control stock" min="0"
                    id="stock_mdlproduct" aria-describedby="emailHelp" placeholder="STOCK">
                <p class="msgError stock_mdlproduct_error"></p>
            </div>
            <div class="col-lg-6 col-md-6 colStockMin mb-3">
                <label for="stock_min_mdlproduct" class="form-label required_field">Stock mínimo</label>
                <input value="0" name="stock_min_mdlproduct" type="number" class="form-control stock_min"
                    min="0" id="stock_min_mdlproduct" placeholder="STOCK MÍNIMO" aria-label="Username"
                    aria-describedby="basic-addon1">
                <p class="msgError stock_min_mdlproduct_error"></p>
            </div>
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="code_factory_mdlproduct" class="form-label">Cod Fábrica</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-industry"></i></span>
                    <input name="code_factory_mdlproduct" type="number" class="form-control code_factory_mdlproduct"
                        id="code_factory_mdlproduct" aria-label="Amount (to the nearest dollar)">
                </div>
                <p class="msgError code_factory_mdlproduct_error"></p>
            </div>
            <div class="col-lg-12 col-md-12">
                <label for="code_bar_mdlproduct" class="form-label">Cod Barras</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                    <input name="code_bar_mdlproduct" type="number" class="form-control code_bar_mdlproduct"
                        id="code_bar_mdlproduct" aria-label="Amount (to the nearest dollar)">
                </div>
                <p class="msgError code_bar_mdlproduct_error"></p>
            </div>
        </div>
    </div>
    <div class="col-lg-6 col-md-6">
        <div class="row">
            <div class="col-lg-12 col-md-12 mb-3">
                <label for="category_id_mdlproduct" class="form-label required_field">Categoría</label>

                <select required name="category_id_mdlproduct" style="text-transform: uppercase;"
                    id="category_id_mdlproduct" class="form-select" aria-label="Default select example">
                    <option value=""></option>
                    @foreach ($categories as $category)
                        <option @if ($category->is_default) selected @endif style="text-transform: uppercase;"
                            value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                <p class="msgError category_id_mdlproduct_error"></p>
            </div>
            <div class="col-lg-12 col-md-12 mb-3">

                <label for="brand" class="form-label required_field">Marca</label>
                <select required name="brand_id_mdlproduct" style="text-transform: uppercase;"
                    id="brand_id_mdlproduct" class="brand form-select" aria-label="Default select example">
                    <option value=""></option>
                    @foreach ($brands as $brand)
                        <option @if ($brand->is_default) selected @endif style="text-transform: uppercase;"
                            value="{{ $brand->id }}">{{ $brand->name }}</option>
                    @endforeach
                </select>
                <p class="msgError brand_id_mdlproduct_error"></p>
            </div>

            <div class="col-lg-12 col-md-12 mb-3">
                <div class="form-group">
                    <label for="image_mdlproduct" class="font-weight-bold" style="font-weight: bold;">IMAGEN</label>
                    <div class="d-flex align-items-center mb-2">
                        <input id="image_mdlproduct" name="image_mdlproduct"
                            class="form-control form-control-sm mr-2" type="file" accept="image/*">
                        <button type="button" class="btn btn-danger btnSetImgDefault" title="Quitar imagen">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                    <span class="image_mdlproduct_error msgError text-danger"></span>
                    <div id="img_preview_container"
                        class="d-flex align-items-center justify-content-center rounded border"
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

<style>
    /* ====== COLORES PARA ICONOS FONT AWESOME DEL FORMULARIO ====== */
    #form_create_product .input-group-text i {
        color: #2563eb;
        font-size: 1.1rem;
        transition: 0.2s ease-in-out;
    }

    #form_create_product .input-group-text:hover i {
        color: #1e40af;
    }

    #form_create_product .btnSetImgDefault i {
        color: white;
        font-size: 1rem;
    }

    #form_create_product .btnSetImgDefault:hover i {
        color: #ffe2e2;
    }
</style>
