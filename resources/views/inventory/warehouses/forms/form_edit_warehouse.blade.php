<form action="" id="formActualizarAlmacen" method="post">
    @csrf
    <input type="hidden" id="almacen_id_edit" name="almacen_id_edit">
    <div class="mb-3">
        <label for="descripcion_edit" style="font-weight: bold;" class="required_field">Nombre del almacén</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-warehouse"></i></span>
            <input required id="descripcion_edit" name="descripcion_edit" type="text" class="form-control"
                style="background-color:#FFF9C4;" placeholder="Nombre del almacén">
        </div>
        <span class="descripcion_edit_error msgError_edit" style="color:red;"></span>
        <small class="text-muted">Solo se edita el nombre. La sede y el tipo no cambian.</small>
    </div>
</form>
