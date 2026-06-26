<form action="" id="formRegistrarAlmacen" method="post">
    @csrf
    <div class="mb-3">
        <label for="descripcion" style="font-weight: bold;" class="required_field">Nombre del almacén</label>
        <div class="input-group">
            <span class="input-group-text"><i class="fa-solid fa-warehouse"></i></span>
            <input required id="descripcion" name="descripcion" type="text" class="form-control"
                style="background-color:#FFF9C4;" placeholder="ej. DEPÓSITO 2">
        </div>
        <span class="descripcion_error msgError" style="color:red;"></span>
        <small class="text-muted">Se crea en la <strong>sede activa</strong> como almacén adicional.</small>
    </div>
</form>
