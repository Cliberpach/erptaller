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
        @include('cash.exit-money.forms.form_create_exit')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            calcularTotalEgreso();
        })
    </script>

    @include('cash.exit-money.create-supplier-modal')
    @include('cash.exit-money.create-proof-payment-modal')
@endsection


@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            events();
        });

        function events() {
            const proofPaymentSelect = document.getElementById('proof_payment');
            const identityDocumentSelect = document.getElementById('identity_document');
            const documentNumberInput = document.getElementById('document_number');

            document.querySelector('#form-create-exit-money').addEventListener('submit', (e) => {
                e.preventDefault();
                storeExitMoney(e.target);
            })

            proofPaymentSelect.addEventListener('change', function() {
                const selectedProofPayment = proofPaymentSelect.options[proofPaymentSelect.selectedIndex]
                    .text;

                if (selectedProofPayment === 'BOLETA ELECTRÓNICA' || selectedProofPayment ===
                    'FACTURA ELECTRÓNICA') {
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

            documentNumberInput.addEventListener('input', function() {
                const identifyDocument = identityDocumentSelect.value;
                const userDocument = documentNumberInput.value;

                if (identifyDocument === 'RUC' && userDocument.length > 11) {
                    documentNumberInput.value = userDocument.slice(0, 11);
                } else if (identifyDocument === 'DNI' && userDocument.length > 8) {
                    documentNumberInput.value = userDocument.slice(0, 8);
                }
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
        }



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

        function calcularTotalEgreso() {
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

        async function storeExitMoney(formStoreExitMoney) {

            toastr.clear();

            const result = await Swal.fire({
                title: '¿Desea registrar la salida de dinero?',
                text: "Confirmar",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'SI, registrar',
                cancelButtonText: 'NO',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            });

            if (result.isConfirmed) {

                try {

                    clearValidationErrors('msgError');

                    Swal.fire({
                        title: 'Registrando salida de dinero...',
                        text: 'Por favor espere',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    const formData = new FormData(formStoreExitMoney);
                    const res = await axios.post(route('tenant.egreso.store'), formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        redirect('tenant.cajas.egreso');
                    } else {
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {

                    Swal.close();

                    if (error.response && error.response.status === 422) {
                        const errors = error.response.data.errors;
                        paintValidationErrors(errors, 'error');
                        return;
                    }

                    toastr.error('Ocurrió un error inesperado', 'ERROR');
                }

            } else {

                Swal.fire({
                    icon: 'info',
                    title: 'Operación cancelada',
                    text: 'No se realizaron acciones.',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'btn btn-secondary'
                    },
                    buttonsStyling: false
                });

            }
        }
    </script>
@endsection
