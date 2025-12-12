<div class="row">
    <div class="col-lg-6 col-md-12 col-xs-12 col-sm-12">

        <!-- DATOS DEL CLIENTE -->
        <div class="row g-3">

            <div class="col-12">
                <!-- Card contenedor -->
                <div class="card border-0 shadow-sm">
                    <div class="card-header py-2 text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información</h6>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">

                            <!-- Cliente -->
                            <div class="col-12">
                                <label class="fw-bold text-muted small">CLIENTE</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-user text-primary me-2"></i>
                                    <p class="mb-0" id="cliente"></p>
                                </div>
                            </div>

                            <!-- Documento -->
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small">DOCUMENTO</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-alt text-success me-2"></i>
                                    <p class="mb-0" id="numero"></p>
                                </div>
                            </div>

                            <!-- Tipo Documento -->
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small">TIPO DOCUMENTO</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-shopping-cart text-primary me-2"></i>
                                    <p class="mb-0" id="type_document"></p>
                                </div>
                            </div>

                            <!-- Monto -->
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small">MONTO</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-money-bill-wave text-success me-2"></i>
                                    <p class="mb-0" id="monto"></p>
                                </div>
                            </div>

                            <!-- Saldo -->
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small">SALDO</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-balance-scale text-warning me-2"></i>
                                    <p class="mb-0" id="saldo"></p>
                                </div>
                            </div>

                            <!-- Estado -->
                            <div class="col-md-6">
                                <label class="fw-bold text-muted small">ESTADO</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-info-circle text-primary me-2"></i>
                                    <p class="mb-0" id="estado"></p>
                                </div>
                            </div>

                            <!-- PDF -->
                            <div class="col-md-6 d-flex align-items-end">
                                <a class="btn btn-danger w-100 btn-sm d-flex justify-content-center align-items-center gap-2 shadow-sm"
                                    id="btn-detalle" target="_blank">
                                    <i class="fas fa-file-pdf fa-lg"></i>
                                    <span>PDF</span>
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- TABLA -->
        <div class="row justify-content-center mt-4">
            <div class="col-12" style="zoom: 85%;">
                <div class="table-responsive">
                    <table class="table-striped table-bordered table-hover dataTables-detalle text-uppercase table">
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
    <div class="col-lg-6 col-md-12 col-xs-12 col-sm-12">
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

                <!-- MÉTODO DE PAGO -->
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <label for="modo_pago" class="fw-bold required_field">MÉTODO DE PAGO</label>
                    <select id="modo_pago" name="modo_pago" class="form-select" onchange="changeModoPago(this)"
                        required>
                        <option value=""></option>
                        @foreach ($payment_methods as $payment_method)
                            <option value="{{ $payment_method->id }}" @if ($payment_method->id == 1) selected @endif>
                                {{ $payment_method->description }}
                            </option>
                        @endforeach
                    </select>
                    <span class="modo_pago_error msgError"></span>
                </div>

                <!-- FECHA -->
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <label for="fecha" class="fw-bold required_field">FECHA</label>
                    <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>"
                        class="form-control" required>
                    <span class="fecha_error msgError"></span>
                </div>

                <!-- EFECTIVO (OCULTO) -->
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <label class="fw-bold required">EFECTIVO</label>
                    <input type="text" id="efectivo_venta" name="efectivo_venta" class="form-control"
                        onkeypress="return parseFloat(event, this);" onkeyup="changeEfectivo()">
                    <span class="efectivo_venta_error msgError"></span>
                </div>

                <!-- IMPORTE -->
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <label for="importe_venta" class="fw-bold required">IMPORTE</label>
                    <input type="text" readonly id="importe_venta" name="importe_venta" value="0.00"
                        class="form-control" onkeypress="return parseFloat(event, this);" onkeyup="changeImporte()"
                        required>
                    <span class="importe_venta_error msgError"></span>
                </div>

                <!-- MONTO -->
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <label for="cantidad" class="fw-bold required_field">MONTO</label>
                    <input type="number" id="cantidad" min="1" value="0.00" name="cantidad"
                        class="form-control" onkeypress="return parseFloat(event, this);" readonly required>
                    <span class="cantidad_error msgError"></span>
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


                <!-- OBSERVACIÓN -->
                <div class="col-12 col-sm-12 col-md-6 col-lg-6">
                    <label for="observacion" class="fw-bold">OBSERVACIÓN</label>
                    <textarea id="observacion" name="observacion" class="form-control" rows="3"></textarea>
                    <span class="observacion_error msgError"></span>
                </div>

                <!-- IMAGEN -->
                <div class="col-12">
                    <label for="imagen" class="fw-bold">IMAGEN</label>
                    <input accept="image/*" data-max-files="1" data-allow-reorder="true" data-max-file-size="3MB"
                        id="imagen" type="file" name="imagen" class="filepond">
                    <span class="imagen_error msgError"></span>
                </div>

            </div>

        </form>
    </div>
</div>
