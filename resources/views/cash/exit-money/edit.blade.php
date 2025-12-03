@extends('layouts.template')

@section('title')
    Egresos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    @if (session('datos'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            {{ session('datos') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

    <div class="card">
        <form action="{{ route('tenant.egreso.update', $exit_money->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-header d-flex justify-content-between flex-row">
                <h4 class="card-title">EDITAR EGRESO</h4>

                <div class="input-group-append">
                    <a href="{{ route('tenant.cajas.egreso') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-start">
                    <div class="form-group mb-3 me-3">
                        <div class="d-flex align-items-center">
                            <label for="proof_payment">Tipo de comprobante </label>
                            <button class="btn btn-rounded p-0" type="button" onclick="openCreateProofPaymentModal()">
                                [<i class='bx bx-plus'></i> Nuevo]
                            </button>
                        </div>
                        <select name="proof_payment" id="proof_payment" class="form-control">
                            @foreach ($proof_payments as $proof_payment)
                                <option value="{{ $proof_payment->id }}"
                                    {{ $exit_money->exit_money == $proof_payment->id ? 'selected' : '' }}>
                                    {{ $proof_payment->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form group me-3">
                        <label for="number">Número</label>
                        <input type="text" name="number" id="number" class="form-control"
                            value="{{ $exit_money->number }}">
                    </div>

                    <div class="form group me-3">
                        <label for="date">Fecha de emisión</label>
                        <input type="date" name="date" id="date" class="form-control"
                            value="{{ $exit_money->date }}">
                    </div>

                    <div class="form-group me-3">
                        <div class="d-flex align-items-center">
                            <label for="supplier_id">Proveedores </label>
                            <button class="btn btn-rounded p-0" type="button" onclick="openCreateSupplierModal()">
                                [<i class='bx bx-plus'></i> Nuevo]
                            </button>
                        </div>
                        <select name="supplier_id" id="supplier_id" class="form-control" style="width: 441.06px;">
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}"
                                    {{ $exit_money->supplier_id == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->document_number }} - {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <button type="button" class="btn btn-primary" onclick="addRow()">Agregar detalle</button>
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
                            @foreach ($exit_money_detail as $exit_money)
                                <tr>
                                    <td width="5%">{{ $loop->iteration }}</td>
                                    <td width="60%">
                                        <input type="text" name="description[]" class="form-control"
                                            value="{{ $exit_money->description }}" oninput="this.value = this.value.toUpperCase()">
                                    </td>
                                    <td width="10%">
                                        <input type="text" name="total[]" class="form-control text-center"
                                            value="{{ $exit_money->total }}" oninput="calcularTotalEgreso()">
                                    </td>
                                    <td></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="text-end mt-4 p-5">
                        <strong>Total acumulado: <span id="total-del-egreso">0.00</span></strong>
                    </div>

                </div>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded',()=>{
            calcularTotalEgreso();
        })
    </script>

    @include('cash.exit-money.create-supplier-modal')
    @include('cash.exit-money.create-proof-payment-modal')
@endsection


@section('js')
    <script>
        let i = 2;

        function calcularTotalEgreso(){
            let total = 0;
            let totalAcumulado = document.getElementById('total-del-egreso');

            document.querySelectorAll('input[name="total[]"]').forEach(function(input) {
            const valor = parseFloat(input.value) || 0;
            total += valor;
            });

            totalAcumulado.innerText = total.toFixed(2);
        }

        function addRow() {
            fila = `
                <tr>
                    <td width="5%">${i++}</td>
                    <td width="60%"><input type="text" name="description[]" class="form-control" oninput="this.value = this.value.toUpperCase()"></td>
                    <td width="10%"><input type="text" name="total[]" class="form-control text-center" oninput="calcularTotalEgreso()"></td>
                    <td>
                        <button type="button" class="btn btn-danger" onclick="deleteRow(this)"><i class='bx bx-trash-alt'></i></button>
                    </td>
                </tr>
            `;

            $("#egreso-detail tbody").append(fila);
            calcularTotalEgreso();
        }

        function deleteRow(button) {
            var row = button.parentNode.parentNode;
            var table = document.getElementById("egreso-detail");
            table.deleteRow(row.rowIndex);
            calcularTotalEgreso();
        }

        function openCreateSupplierModal() {
            $('#createSupplierModal').modal('toggle');
        }

        function openCreateProofPaymentModal() {
            $('#createProofPaymentModal').modal('toggle');
        }
    </script>
@endsection
