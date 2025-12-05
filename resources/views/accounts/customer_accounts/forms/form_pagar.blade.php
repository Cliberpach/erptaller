<div class="row">
    <div class="col-md-6">

        <!-- DATOS DEL CLIENTE -->
        <div class="row g-3">

            <div class="col-12">
                <label for="cliente" class="required fw-bold">CLIENTE</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">
                        <i class="fa fa-user text-primary"></i>
                    </span>
                    <input type="text" id="cliente" name="cliente" class="form-control" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <label class="required fw-bold" for="numero">DOCUMENTO</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">
                        <i class="fa fa-file-alt text-success"></i>
                    </span>
                    <input type="text" id="numero" name="numero" class="form-control" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <label class="required fw-bold" for="type_document">TIPO DOCUMENTO</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">
                        <i class="fa fa-shopping-cart text-primary"></i>
                    </span>
                    <input type="text" id="type_document" name="type_document" class="form-control" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <label class="required fw-bold" for="monto">MONTO</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">
                        <i class="fas fa-money-bill-wave text-success"></i>
                    </span>
                    <input type="text" id="monto" name="monto" class="form-control" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <label class="required fw-bold" for="saldo">SALDO</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">
                        <i class="fa fa-balance-scale text-warning"></i>
                    </span>
                    <input type="text" id="saldo" name="saldo" class="form-control" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <label class="required fw-bold" for="estado">ESTADO</label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light">
                        <i class="fa fa-info-circle text-primary"></i>
                    </span>
                    <input type="text" id="estado" name="estado" class="form-control" disabled>
                </div>
            </div>

            <div class="col-md-6">
                <a class="btn btn-danger btn-sm" id="btn-detalle" target="_blank">
                    <i class="fa fa-file-pdf-o"></i>
                </a>
            </div>
        </div>

        <!-- TABLA -->
        <div class="row justify-content-center mt-4">
            <div class="col-12" style="zoom: 85%;">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-hover dataTables-detalle text-uppercase">
                        <thead>
                            <tr>
                                <th class="text-center">Fecha</th>
                                <th class="text-center">Observación</th>
                                <th class="text-center">Monto</th>
                                <th class="text-center">Imágen</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- FORM DE REGISTRO DE DETALLE -->
    <div class="col-md-6">
        <form id="frmDetalle" method="POST" enctype="multipart/form-data">
            {{ csrf_field() }}

          <div class="row g-4">

            <!-- PAGO -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="pago" class="fw-bold required_field">PAGO</label>
                <select id="pago" name="pago" class="form-select" required onchange="tipoPago(this)">
                    <option value="A CUENTA">A CUENTA</option>
                    <option value="TODO">TODO</option>
                </select>
                <span class="pago_error msgError"></span>
            </div>

            <!-- FECHA -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="fecha" class="fw-bold required_field">FECHA</label>
                <input type="date" id="fecha" name="fecha"
                    value="<?= date('Y-m-d') ?>" class="form-control" required>
                <span class="fecha_error msgError"></span>
            </div>

            <!-- MONTO -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="cantidad" class="fw-bold required_field">MONTO</label>
                <input type="number" id="cantidad" min="1" value="0.00" name="cantidad"
                    class="form-control" onkeypress="return parseFloat(event, this);" readonly required>
                <span class="cantidad_error msgError"></span>
            </div>

            <!-- OBSERVACIÓN -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="observacion" class="fw-bold">OBSERVACIÓN</label>
                <textarea id="observacion" name="observacion" class="form-control" rows="3"></textarea>
                <span class="observacion_error msgError"></span>
            </div>

            <!-- EFECTIVO (OCULTO) -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6 d-none">
                <label class="fw-bold required">EFECTIVO</label>
                <input type="text" id="efectivo_venta" name="efectivo_venta"
                    class="form-control"
                    onkeypress="return parseFloat(event, this);" onkeyup="changeEfectivo()">
                <span class="efectivo_venta_error msgError"></span>
            </div>

            <!-- MÉTODO DE PAGO -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="modo_pago" class="fw-bold required_field">MÉTODO DE PAGO</label>
                <select id="modo_pago" name="modo_pago" class="form-select"
                        onchange="changeModoPago(this)" required>
                    <option value=""></option>
                    @foreach ($payment_methods as $payment_method)
                        <option value="{{ $payment_method->id }}"
                            @if ($payment_method->id == 1) selected @endif>
                            {{ $payment_method->description }}
                        </option>
                    @endforeach
                </select>
                <span class="modo_pago_error msgError"></span>
            </div>

            <!-- CUENTA -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="cuenta" class="fw-bold required">CUENTA</label>
                <select id="cuenta" name="cuenta" class="form-select">
                    <option value="">SELECCIONAR</option>
                </select>
                <span class="cuenta_error msgError"></span>
            </div>

            <!-- N° OPERACIÓN -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="nro_operacion" class="fw-bold required">N° OPERACIÓN</label>
                <input type="text" maxlength="20" id="nro_operacion" name="nro_operacion"
                    class="form-control">
                <span class="nro_operacion_error msgError"></span>
            </div>

            <!-- IMPORTE -->
            <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                <label for="importe_venta" class="fw-bold required">IMPORTE</label>
                <input type="text" readonly id="importe_venta" name="importe_venta"
                    value="0.00" class="form-control"
                    onkeypress="return parseFloat(event, this);" 
                    onkeyup="changeImporte()" required>
                <span class="importe_venta_error msgError"></span>
            </div>

            <!-- IMAGEN -->
            <div class="col-12">
                <label for="imagen" class="fw-bold">IMAGEN</label>
                <input id="imagen" type="file" name="imagen" class="form-control" accept="image/*">
                <span class="imagen_error msgError"></span>
            </div>

            <div class="col-12 text-end">
                <a href="javascript:void(0);" id="limpiar_imagen" class="badge bg-danger">x</a>
            </div>

            <div class="col-12 text-center">
                <img class="imagen img-fluid"
                    style="max-height: 250px; object-fit: contain;"
                    src="{{ asset('assets/img/img_default.png') }}" alt="">
                <input id="url_imagen" name="url_imagen" type="hidden">
            </div>

        </div>

        </form>
    </div>
</div>
