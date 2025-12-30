<form action="" id="formUpdate" method="post">
    <div class="row">
        @csrf

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="tipo" class="required_field mb-2" style="font-weight: bold;">TIPO</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-file-signature"></i>
                </span>
                <select id="tipo" name="tipo" class="form-select" aria-label="Default select example" onchange="changeTipoPago()">

                    <option value="1"
                    @if ($condicion_pago->type == 'CONTADO')
                        selected
                    @endif
                    >CONTADO</option>

                    <option value="2"
                    @if ($condicion_pago->type == 'CREDITO')
                        selected
                    @endif>CRÉDITO</option>
                </select>
             </div>
            <span class="tipo_error msgError"  style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="nro_dias" class="required_field mb-2" style="font-weight: bold;">N° DÍAS</label>
            <div class="input-group mb-3">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-calendar-days"></i>
                </span>
                <input
                @if ($condicion_pago->type == 'CONTADO')
                        readonly
                @endif
                value="{{$condicion_pago->nro_days}}" required id="nro_dias" maxlength="20"  name="nro_dias" type="text" class="form-control inputEnteroPositivo" placeholder="N° días" aria-label="Username" aria-describedby="basic-addon1">
            </div>
            <span class="nro_dias_error msgError"  style="color:red;"></span>
        </div>

    </div>
</form>
