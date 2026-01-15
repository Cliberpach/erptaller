<form action="" id="form-store">
    @csrf

    <div class="row">
        <!-- Comprobante -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
            <label class="form-label fw-bold required_field">Comprobante:</label>

            <select class="form-control" id="type_sale" name="type_sale" required>
                @foreach ($invoice_types as $item)
                    <option @if ($item->name === 'NOTA DE VENTA') selected @endif value="{{ $item->id }}">
                        {{ $item->name }}</option>
                @endforeach
            </select>
            <p class="type_sale_error msgError mb-0"></p>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
            <label class="form-label fw-bold required_field">Condición:</label>
            <select class="form-control" id="payment_condition_id" name="payment_condition_id" required>
                @foreach ($payment_conditions as $payment_condition)
                    <option value="{{ $payment_condition->id }}" data-days="{{ $payment_condition->nro_days ?? 0 }}"
                        @if ($payment_condition->name === 'CONTADO') selected @endif>

                        @if ($payment_condition->type === 'CONTADO')
                            {{ $payment_condition->name }}
                        @else
                            {{ $payment_condition->name . '-' . $payment_condition->nro_days . ' DÍAS ' }}
                        @endif

                    </option>
                @endforeach
            </select>
            <p class="payment_condition_id_error msgError mb-0"></p>
        </div>

        <!-- Fecha Registro -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
            <label class="form-label fw-bold">Fecha Registro:</label>
            <input type="date" class="form-control" name="registration_date" id="registration_date"
                value="{{ date('Y-m-d') }}" readonly>
        </div>

        <!-- Fecha Vencimiento -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
            <label class="form-label fw-bold">Fecha Vencimiento:</label>
            <input type="date" class="form-control" name="expiration_date" id="expiration_date"
                value="{{ date('Y-m-d') }}">
        </div>

        <!-- Cliente -->
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 mb-3">
            <label class="form-label fw-bold required_field">Cliente:</label>
            <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlNewCustomer();" style="margin-left:4px;"></i>

            <select class="form-control" id="customer_id" name="customer_id" required>
                <option value="">Seleccionar</option>
            </select>
            <p class="customer_id_error msgError mb-0"></p>
        </div>

        <!-- Vehículo -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
            <label class="form-label fw-bold">Vehículo:</label>
            <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateVehicle();"
                style="margin-left:4px;"></i>

            <select class="form-control" id="vehicle_id" name="vehicle_id">
                <option value="">Seleccionar</option>
            </select>
            <p class="vehicle_id_error msgError mb-0"></p>
        </div>

        <!-- Placa -->
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 mb-3">
            <label class="form-label fw-bold">Placa:</label>
            <input type="text" class="form-control text-uppercase" id="plate" name="plate" maxlength="8"
                minlength="6" placeholder="Ej: ABC123">

            <div id="vehicle_info" class="d-none bg-light mt-2 rounded border p-2">
                <i class="fas fa-car text-primary me-1"></i>
                <span class="fw-semibold"></span>
                <span class="text-muted"></span>
            </div>

            <p class="plate_error msgError mb-0"></p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-7 col-md-12 col-sm-12 col-xs-12">
            <div class="table-responsive">
                @include('sales.sale_document.tables.tbl_products')
            </div>
        </div>
        <div class="col-lg-5 col-md-12 col-sm-12 col-xs-12">

            <div class="row sale-detail">
                <div class="col sale-detail__col">
                    @include('sales.sale_document.tables.tbl_sale_detail')
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-lg-6" style="display:flex;justify-content:end;">
                            <span style="font-weight:bold;">OP. GRAVADA</span>
                        </div>
                        <div class="col-lg-6" style="display:flex;justify-content:end;">
                            <p class="op-amount"></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-6" style="display:flex;justify-content:end;">
                            <span style="font-weight:bold;">IGV</span>
                        </div>
                        <div class="col-lg-6" style="display:flex;justify-content:end;">
                            <p class="igv-amount"></p>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="table-responsive">
                            @include('sales.sale_document.tables.tbl_pay')
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <button class="btn btn-primary btnAddPay">
                                <i class="fas fa-plus"></i> Agregar pago
                            </button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" form="form-store"
                                class="btn btn-dark btn-lg w-100 payment__total rounded-4 d-flex justify-content-between align-items-center p-4 text-white shadow-lg">

                                <div class="d-flex align-items-center text-uppercase fw-semibold gap-2 opacity-75">
                                    <i class="fas fa-cash-register text-success fs-3"></i>
                                    <span>Total</span>
                                </div>

                                <div class="d-flex align-items-end">
                                    <span class="fs-4 me-1">S/</span>
                                    <span class="total-amount fw-bold display-5">0.00</span>
                                </div>

                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</form>
