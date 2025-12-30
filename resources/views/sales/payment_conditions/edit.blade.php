@extends('layouts.template')
@section('title')
    EDITAR CONDICIÓN PAGO
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">Editar Condición de pago</h4>

            <div class="d-flex flex-wrap gap-2">

            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include('sales.payment_conditions.forms.form_edit')
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
                    <button class="btn btn-primary" form="formUpdate" type="submit">
                        <i class="fas fa-save"></i> ACTUALIZAR
                    </button>

                </div>

            </div>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', () => {
        iniciarSelect2();
        events();
    })

    function events() {

        document.querySelector('#formUpdate').addEventListener('submit', (e) => {
            e.preventDefault();
            update(e.target);
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

    function update(formUpdate) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA ACTUALIZAR LA MODALIDAD DE PAGO?",
            text: "Se actualizaran los datos de la Condicion de pago!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(formUpdate);

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando Condicion de pago...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const id = @json($condicion_pago->id);
                    let urlUpdate = `{{ route('tenant.ventas.condiciones_pago.update', ['id' => ':id']) }}`;
                    urlUpdate = urlUpdate.replace(':id', id);

                    const response = await fetch(urlUpdate, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
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
                        const condicion_pago_index = @json(route('tenant.ventas.condiciones_pago.index'));
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = condicion_pago_index;
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR CONDICIÓN DE PAGO');
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
            toastr.error('EL TIPO DE MODALIDAD DE PAGO NO ES VÁLIDO');
            return;
        }

        if (tipo == 2) {
            inputNroDias.readOnly = false;
            inputNroDias.value = 1;
        }
    }
</script>
