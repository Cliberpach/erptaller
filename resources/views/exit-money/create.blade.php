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
<form action="{{ route('tenant.egreso.store') }}" method="POST">
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
            </div>
    
            <!-- Número -->
            <div class="col-md-4">
                <label for="number" class="form-label">Número</label>
                <input type="text" name="number" id="number" class="form-control">
            </div>
    
            <!-- Fecha de emisión -->
            <div class="col-md-4">
                <label for="date" class="form-label">Fecha de emisión</label>
                <input type="date" name="date" id="date" class="form-control" value="{{ $date }}" readonly>
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
            </div>
    
            <!-- Tipo de Pago -->
            <div class="col-md-3">
                <label for="payment_type" class="form-label">Tipo de pago</label>
                <select name="payment_type" id="payment_type" class="form-control">
                    <option value="EFECTIVO">Efectivo</option>
                    <option value="YAPE">Yape</option>
                    <option value="PLIN">Plin</option>
                </select>
            </div>
        </div>
    
        <!-- Botón para agregar detalles -->
        <div class="text-end mt-4">
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
                        <td width="60%"><input type="text" name="description[]" class="form-control" oninput="this.value = this.value.toUpperCase()"></td>
                        <td width="10%"><input type="text" name="total[]" class="form-control text-center" oninput="calcularTotalEgreso()"></td>
                        <td></td>
                    </tr>
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

    @include('exit-money.create-supplier-modal') 
    @include('exit-money.create-proof-payment-modal')
@endsection


@section('js')
    <script>
        let i = 2;

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

        function calcularTotalEgreso(){
            let total = 0;
            let totalAcumulado = document.getElementById('total-del-egreso');

            document.querySelectorAll('input[name="total[]"]').forEach(function(input) {
            const valor = parseFloat(input.value) || 0;  
            total += valor; 
            });

            totalAcumulado.innerText = total.toFixed(2);
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const proofPaymentSelect = document.getElementById('proof_payment');
        const identityDocumentSelect = document.getElementById('identity_document');
        const documentNumberInput = document.getElementById('document_number');

        proofPaymentSelect.addEventListener('change', function () {
            const selectedProofPayment = proofPaymentSelect.options[proofPaymentSelect.selectedIndex].text;

            if (selectedProofPayment === 'BOLETA ELECTRÓNICA' || selectedProofPayment === 'FACTURA ELECTRÓNICA') {
                identityDocumentSelect.innerHTML = `
                    <option value="DNI">DNI</option>
                    <option value="RUC">RUC</option>
                `;
            } else {
                identityDocumentSelect.innerHTML = `
                    <option value="DNI">DNI</option>
                `;
            }
        });


        documentNumberInput.addEventListener('input', function () {
            const identifyDocument = identityDocumentSelect.value;
            const userDocument = documentNumberInput.value;

            if (identifyDocument === 'RUC' && userDocument.length > 11) {
                documentNumberInput.value = userDocument.slice(0, 11);
            } else if (identifyDocument === 'DNI' && userDocument.length > 8) {
                documentNumberInput.value = userDocument.slice(0, 8);
            }
        });
    });

    document.addEventListener('click', function(event) {
        if (event.target && event.target.id === 'btn_consulta_sunat') {
            const userDocument = document.getElementById('document_number').value;
            const identifyDocument = document.getElementById('identity_document').value;

            if (identifyDocument === 'RUC' && userDocument.length === 11) {
                Swal.fire({
                    title: 'Consultar',
                    text: "¿Desea consultar RUC a Sunat?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: "#696cff",
                    confirmButtonText: 'Si, Confirmar',
                    cancelButtonText: "No, Cancelar",
                    showLoaderOnConfirm: true,
                    preConfirm: function() {
                        var url = '/landlord/ruc/' + userDocument;
                        return fetch(url, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        }).then(response => response.json())
                          .catch(error => {
                              console.error('Error al consultar la API:', error);
                              Swal.fire({
                                  icon: 'error',
                                  title: 'Error',
                                  text: 'Hubo un problema al consultar la API.'
                              });
                          });
                    },
                    allowOutsideClick: function() {
                        return !Swal.isLoading();
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var data = result.value;
                        if (data.success === false) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'RUC inválido o no existe!'
                            });
                        } else {
                            document.getElementById('name').value = data.data.nombre_o_razon_social;
                            document.getElementById('address').value = data.data.direccion;
                        }
                    }
                }).catch(function(error) {
                    console.error('Error al consultar la API:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al consultar la API.'
                    });
                });
            } else if (identifyDocument === 'DNI' && userDocument.length === 8) {
                Swal.fire({
                    title: 'Consultar',
                    text: "¿Desea consultar DNI a Sunat?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: "#696cff",
                    confirmButtonText: 'Si, Confirmar',
                    cancelButtonText: "No, Cancelar",
                    showLoaderOnConfirm: true,
                    preConfirm: function() {
                        var url = '/landlord/dni/' + userDocument;
                        return fetch(url, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        }).then(response => response.json())
                          .catch(error => {
                              console.error('Error al consultar la API:', error);
                              Swal.fire({
                                  icon: 'error',
                                  title: 'Error',
                                  text: 'Hubo un problema al consultar la API.'
                              });
                          });
                    },
                    allowOutsideClick: function() {
                        return !Swal.isLoading();
                    }
                }).then(function(result) {
                    if (result.isConfirmed) {
                        var data = result.value;
                        if (data.success === false) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'DNI inválido o no existe!'
                            });
                        } else {
                            document.getElementById('name').value = data.data.nombre_completo;
                            document.getElementById('address').value = '';
                        }
                    }
                }).catch(function(error) {
                    console.error('Error al consultar la API:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al consultar la API.'
                    });
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'Mala escritura de RUC o DNI'
                });
            }
        }
    });
</script>

@endsection
