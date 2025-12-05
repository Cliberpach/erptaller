@extends('layouts.template')

@section('title')
    ASIGNAR CUENTAS BANCARIAS
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')
<div class="card">
    @csrf
    <div class="card-header d-flex flex-row justify-content-between">
        <h4 class="card-title">Asignar cuentas bancarias <i class="fas fa-wallet" style="color: rgb(7, 45, 168);"></i></h4>
        <div class="input-group-append">
            
        </div>
    </div>
<div class="card-body">
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-primary" role="alert">
                <strong>Método de Pago:</strong> {{ $tipo_pago->description }} <br>
                <strong>Creado:</strong> {{ $tipo_pago->created_at }}
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="table-responsive">
                @include('sales.payment_method.tables.tbl_asignar_cuentas')
            </div>
        </div>
    </div>
</div>

    <div class="card-footer">
        <div class="d-flex justify-content-end">
            <button class="btn btn-danger btn-volver mr-1">
            <i class="fas fa-arrow-left"></i> VOLVER
        </button>
        <button class="btn btn-success btn-asignar-cuentas">
            <i class="fas fa-save"></i> REGISTRAR
        </button>
        </div>
    </div>

</div>
@endsection


<script>
    const lstCuentasAsignadas = [];
    const cuentasBD = @json($cuentas);
    let dtCuentasAsignar = null;

    document.addEventListener('DOMContentLoaded', () => {
        dtCuentasAsignar = loadDataTableSimple('tbl_asignar_cuentas');
        agregarCuentasPrevias();
        events();
    })

    function events() {
        document.addEventListener('click', function(e) {

            if (e.target.closest('.chk-cuenta')) {

                const checkbox = e.target.closest('.chk-cuenta');
                const cuentaId = parseInt(checkbox.getAttribute('data-id'));

                if (checkbox.checked) {
                    if (!lstCuentasAsignadas.includes(cuentaId)) {
                        lstCuentasAsignadas.push(cuentaId);
                    }
                } else {
                    const index = lstCuentasAsignadas.indexOf(cuentaId);
                    if (index !== -1) {
                        lstCuentasAsignadas.splice(index, 1);
                    }
                }

            }

            if (e.target.closest('.btn-asignar-cuentas')) {
                asignarCuentas();
            }

            if (e.target.closest('.btn-volver')) {
                window.location.href = @json(route('tenant.ventas.metodo_pago'));
            }

        });
    }

    function agregarCuentasPrevias() {
        const cuentas_asignadas = @json($cuentas_asignadas);
        cuentas_asignadas.forEach((ca) => {
            lstCuentasAsignadas.push(ca.bank_account_id);
        })
    }

    function asignarCuentas() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA ASIGNAR LAS CUENTAS BANCARIAS AL MÉTODO DE PAGO?",
            text: "Se realizará la asignación!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ASIGNAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;

                const formData = new FormData();
                formData.append('lstCuentasAsignadas', JSON.stringify(lstCuentasAsignadas));
                formData.append('tipo_pago_id', @json($tipo_pago->id));

                const urlAsignarCuentas = @json(route('tenant.ventas.metodo_pago.assignAccountsStore'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Asignando cuentas bancarias...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(urlAsignarCuentas, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = @json(route('tenant.ventas.metodo_pago'));
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ASIGNAR CUENTAS BANCARIAS');
                    Swal.close();
                } finally {
                }

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire({
                    title: "OPERACIÓN CANCELADA",
                    text: "NO SE REALIZARON ACCIONES",
                    icon: "error"
                });
            }
        });
    }
</script>
