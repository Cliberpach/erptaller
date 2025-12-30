@extends('layouts.template')
@section('title')
    REGISTRAR CONDICIÓN PAGO
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">Registrar Condición de pago</h4>

            <div class="d-flex flex-wrap gap-2">

            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include('sales.payment_conditions.forms.form_create')
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-12 d-flex justify-content-end">

                    <!-- BOTÓN VOLVER -->
                    <button type="button" class="btn btn-danger me-1"
                        onclick="redirect('tenant.ventas.condiciones_pago.index')">
                        <i class="fas fa-arrow-left"></i> VOLVER
                    </button>

                    <!-- BOTÓN REGISTRAR -->
                    <button class="btn btn-primary" form="formStore" type="submit">
                        <i class="fas fa-save"></i> REGISTRAR
                    </button>

                </div>

            </div>
        </div>
    </div>
@endsection

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        iniciarSelect2();
        events();
    })

    function events() {

        document.querySelector('#formStore').addEventListener('submit', (e) => {
            e.preventDefault();
            store();
        })

        document.addEventListener('click', (e) => {
            if (e.target.closest('.btnVolver')) {
                const rutaIndex = '{{ route('tenant.ventas.condiciones_pago.index') }}';
                window.location.href = rutaIndex;
            }
        })

    }

    function iniciarSelect2() {
        $('.select2_form').select2({
            theme: "bootstrap-5",
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
        });

    }

    function store() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA REGISTRAR LA CONDICION DE PAGO?",
            text: "Se creará una nueva CONDICION de pago!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                clearValidationErrors('msgError');
                const token                 = document.querySelector('input[name="_token"]').value;
                const formStore             = document.querySelector('#formStore');
                const formData              = new FormData(formStore);
                const urlRegistrarTipoPago  = @json(route('tenant.ventas.condiciones_pago.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando Condición de pago...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(urlRegistrarTipoPago, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    console.log(res);

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        const tipopago_index = @json(route('tenant.ventas.condiciones_pago.index'));
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = tipopago_index;
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR CONDICION DE PAGO');
                    Swal.close();
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

    function changeTipoPago() {
        const tipo = document.querySelector('#tipo').value;
        const inputNroDias = document.querySelector('#nro_dias');

        inputNroDias.readOnly = true;
        inputNroDias.value = 0;

        if (!tipo) {
            toastr.error('EL TIPO DE CONDICION DE PAGO NO ES VÁLIDO');
            return;
        }

        if (tipo == 2) {
            inputNroDias.readOnly = false;
            inputNroDias.value = 1;
        }
    }
</script>
@endsection
