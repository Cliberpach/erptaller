<form id="form-create-exit-money" method="POST">
    @csrf
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-title mb-0">Registrar Egreso</h4>
        <div>
            <a href="{{ route('tenant.cajas.egreso') }}" class="btn btn-secondary me-2">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-3">

            <!-- Tipo de Comprobante -->
            <div class="col-md-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="proof_payment" class="form-label mb-0">Tipo de comprobante</label>
                    <button class="btn btn-sm btn-link p-0" type="button" onclick="openCreateProofPaymentModal()">
                        [+ Nuevo]
                    </button>
                </div>
                <select name="proof_payment" id="proof_payment" class="form-control">
                    @foreach ($proof_payments as $proof_payment)
                        <option value="{{ $proof_payment->id }}">{{ $proof_payment->description }}</option>
                    @endforeach
                </select>
                <p class="proof_payment_error msgError"></p>
            </div>

            <!-- Número -->
            <div class="col-md-4">
                <label for="number" class="form-label">Número</label>
                <input type="text" name="number" id="number" class="form-control">
                <p class="number_error msgError"></p>
            </div>

            <!-- Fecha de emisión -->
            <div class="col-md-4">
                <label for="date" class="form-label">Fecha de emisión</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $date }}"
                    readonly>
                <p class="date_error msgError"></p>
            </div>

        </div>

        <div class="row mb-3">

            <!-- Proveedores -->
            <div class="col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label for="supplier_id" class="form-label mb-0">Proveedores</label>
                    <button class="btn btn-sm btn-link p-0" type="button" onclick="openCreateSupplierModal()">
                        [+ Nuevo]
                    </button>
                </div>
                <select name="supplier_id" id="supplier_id" class="form-control">
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">
                            {{ $supplier->document_number }} - {{ $supplier->name }}
                        </option>
                    @endforeach
                </select>
                <p class="supplier_id_error msgError"></p>
            </div>

            <!-- Razón -->
            <div class="col-md-3">
                <label for="reason" class="form-label">Razón</label>
                <select name="reason" id="reason" class="form-control">
                    <option value="GASTO">GASTO</option>
                    <option value="DEVOLUCION">DEVOLUCION</option>
                    <option value="COMPRAS">COMPRAS</option>
                    <option value="LIMPIEZA">LIMPIEZA</option>
                    <option value="ENVIO">ENVIO</option>
                </select>
                <p class="reason_error msgError"></p>
            </div>

            <!-- Tipo de Pago -->
            <div class="col-md-3">
                <label for="payment_method_id" class="form-label">MÉTODO DE PAGO</label>
                <select name="payment_method_id" id="payment_method_id" class="form-control">
                    @foreach ($payment_methods as $payment_method)
                        <option value="{{$payment_method->id}}">{{$payment_method->description}}</option>
                    @endforeach
                </select>
                <p class="payment_method_id_error msgError"></p>
            </div>

        </div>

        <!-- Botón para agregar detalles -->
        <div class="mt-4 text-end">
            <button type="button" class="btn btn-primary" onclick="addRow()">Agregar Detalle</button>
        </div>
    </div>

    <div class="row">
        <div class="col">

            <table id="egreso-detail" style="width:100%" class="table-hover table">
                <thead>
                    <tr>
                        <th width="10%">#</th>
                        <th>Descripción</th>
                        <th width="20%">Total</th>
                        <th width="10%"></th>
                    </tr>
                </thead>

                <tbody class="body-table">
                    <tr>
                        <td width="5%">1</td>
                        <td width="60%">
                            <input type="text" name="description[]" class="form-control"
                                oninput="this.value = this.value.toUpperCase()">
                        </td>
                        <td width="10%">
                            <input type="text" name="total[]" class="form-control text-center"
                                oninput="calcularTotalEgreso()">
                        </td>
                        <td></td>
                    </tr>
                </tbody>
            </table>

            <p class="description_error msgError"></p>
            <p class="total_error msgError"></p>

            <div class="mt-4 p-5 text-end">
                <strong>Total acumulado: <span id="total-del-egreso">0.00</span></strong>
            </div>

        </div>
    </div>

</form>
