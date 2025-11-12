<form action="" id="formEditItem" method="post">    
    @csrf
    <div class="row">
        <div class="col-12 mb-3">
            <label class="required_field" for="item_nombre_edit" style="font-weight: bold;">PRODUCTO</label>
            <input required disabled type="text" id="item_nombre_edit" class="form-control">
        </div>
        <div class="col-12 mb-3">
            <label class="required_field" for="item_unidad_edit" style="font-weight: bold;">UNIDAD</label>
            <input required disabled type="text" id="item_unidad_edit" class="form-control">
        </div>
        <div class="col-12">
            <label class="required_field" for="item_cantidad_edit" style="font-weight: bold;">CANTIDAD</label>
            <input required type="text" id="item_cantidad_edit" class="form-control inputEnteroPositivo">
        </div>
    </div>
</form> 