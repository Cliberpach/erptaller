<form id="formPagar" method="POST" enctype="multipart/form-data">
    {{ csrf_field() }}
    <div class="row">

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" for="pago" class="form-label required_field">Pago</label>
            <select name="pago" id="pago" class="select2_mdl_pago form-select" required>
                <option value="A CUENTA">A CUENTA</option>
                <option value="TODO">TODO</option>
            </select>
            <span class="pago_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" for="metodo_pago" class="form-label required_field">Método de
                pago</label>
            <select data-placeholder="Seleccionar" name="metodo_pago" id="metodo_pago"
                class="select2_mdl_pago form-select" required>
                <option></option>
                @foreach ($payment_methods as $metodo_pago)
                    <option value="{{ $metodo_pago->id }}">
                        {{ $metodo_pago->description }}</option>
                @endforeach
            </select>
            <span class="metodo_pago_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" for="fecha" class="form-label required_field">Fecha</label>
            <input type="date" name="fecha" id="fecha" class="form-control" value="<?= date('Y-m-d') ?>"
                required>
            <span class="fecha_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label for="cuenta" class="fw-bold required">CUENTA</label>
            <select id="cuenta" name="cuenta" class="form-select">
                <option value="">SELECCIONAR</option>
            </select>
            <span class="cuenta_error msgError"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" for="cantidad" class="form-label required_field">Cantidad</label>
            <input type="number" min="1" name="cantidad" id="cantidad" value="0.00"
                class="form-control colorReadOnly" onkeypress="return filterFloat(event, this);" readonly required>
            <span class="cantidad_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" for="efectivo_venta" class="form-label required_field">Efectivo</label>
            <input readonly type="text" value="0" class="form-control inputDecimalPositivo colorReadOnly"
                id="efectivo_venta" onkeyup="changeEfectivo(this)" name="efectivo_venta" required>
            <span class="efectivo_venta_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label for="nro_operacion" class="fw-bold required">N° OPERACIÓN</label>
            <input type="text" maxlength="20" id="nro_operacion" name="nro_operacion" class="form-control">
            <span class="nro_operacion_error msgError"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" for="importe_venta" class="form-label required_field">Importe</label>
            <input readonly type="text" class="form-control inputDecimalPositivo colorReadOnly" id="importe_venta"
                value="0.00" onkeyup="changeImporte(this)" name="importe_venta" required>
            <span class="importe_venta_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" for="observacion" class="form-label">Observacion</label>
            <textarea name="observacion" id="observacion" cols="30" rows="3" class="form-control"></textarea>
            <span class="observacion_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
            <div>
                <label for="img_pago" style="font-weight:bold;" class="form-label">IMAGEN</label>
                <input id="img_pago" name="img_pago" class="form-control form-control-sm" type="file"
                    accept="image/*">
            </div>
            <span class="img_pago_error msgError" style="color:red;"></span>
        </div>

    </div>
</form>
