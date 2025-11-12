<form action="" id="formEditItem" method="post">    
    @csrf
    <div class="row">
        <div class="col-12 mb-3">
            <label class="required_field" for="item_nombre_edit" style="font-weight: bold;">PRODUCTO</label>
            <input required disabled type="text" id="item_nombre_edit" class="form-control">
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label for="item_precio_edit" style="font-weight: bold;">PRECIO</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-money-bill-1-wave"></i>                                    
                </span>
                <input id="item_precio_edit" name="item_precio_edit" type="text" class="form-control inputDecimalPositivo" placeholder="Precio" aria-label="Username" aria-describedby="basic-addon1">
              </div>
        </div>
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label for="item_cantidad_edit" style="font-weight: bold;">CANTIDAD</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-box-open"></i>                                    
                </span>
                <input id="item_cantidad_edit" name="item_cantidad_edit" type="text" class="form-control inputDecimalPositivo" placeholder="Cantidad" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>
    </div>
</form> 