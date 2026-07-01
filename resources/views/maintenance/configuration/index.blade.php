@extends('layouts.template')

@section('title')
    Configuración
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <div class="card">
        @csrf
        <div class="card-header d-flex justify-content-between flex-row">
            <h4 class="card-title">Configuración</h4>
        </div>
        <div class="card-body">

            @include('maintenance.configuration.forms.form_configuration')

        </div>
        <div class="card-footer" style="display:flex;justify-content:end;">
            <button type="submit" class="btn btn-primary" form="frmConfiguration">
                <i class="fas fa-save"></i> GUARDAR
            </button>
        </div>
    </div>

    {{-- ZONA DE PELIGRO: sección aparte, FUERA del form. Esqueleto vacío -> las acciones
         destructivas (borrar tablas, resetear) se definen en una tarea posterior. --}}
    <div class="card border-danger mt-4">
        <div class="card-header bg-danger text-white d-flex align-items-center">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <h4 class="card-title mb-0">Zona de Peligro</h4>
        </div>
        <div class="card-body">
            <p class="text-muted mb-0">Sin acciones disponibles aún.</p>
        </div>
    </div>
@endsection

<script>
    document.addEventListener('DOMContentLoaded', () => {
        events();
    })

    function events() {
        document.querySelector('#frmConfiguration').addEventListener('submit', (e) => {
            e.preventDefault();
            saveConfiguration(e.target);
        })
    }

    function saveConfiguration(formConfiguration) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA GUARDAR LA CONFIGURACIÓN?",
            text: "Se guardarán los cambios!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, GUARDAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Guardando...',
                    html: 'Registrando configuración...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const token = document.querySelector('input[name="_token"]').value;
                    const formData = new FormData(formConfiguration);
                    const urlSaveConfiguration = @json(route('tenant.mantenimientos.configuracion.store'));


                    const response = await fetch(urlSaveConfiguration, {
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
                        Swal.close();

                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR AL GUARDAR LA CONFIGURACIÓN');
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
</script>
<script src="{{ asset('assets/js/utils.js') }}"></script>
