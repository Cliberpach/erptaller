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
@endsection

@section('js')
<script>
    let tipoLimpiar = null;

    document.addEventListener('DOMContentLoaded', () => {
        events();
    })

    function events() {
        document.querySelector('#frmConfiguration').addEventListener('submit', (e) => {
            e.preventDefault();
            saveConfiguration(e.target);
        })

        document.getElementById('input_confirmar_limpiar').addEventListener('input', (e) => {
            document.getElementById('btn_confirmar_limpiar').disabled = e.target.value.trim() !== 'ELIMINAR';
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

    function abrirModalLimpiar(tipo) {
        tipoLimpiar = tipo;

        const textos = {
            documentos: 'Se eliminarán TODOS los documentos de venta, compra y órdenes de trabajo. Se conserva el catálogo (productos, clientes, almacenes). Esta operación NO se puede deshacer.',
            todo: 'Se eliminará TODA la información del tenant (catálogo, documentos, usuarios) y se reiniciará con los datos base (seeders) + un usuario admin por defecto. Esta operación NO se puede deshacer.'
        };
        const titulos = {
            documentos: 'Eliminar Documentos',
            todo: 'Eliminar TODO'
        };

        document.getElementById('modal_confirmar_limpiar_titulo').textContent = titulos[tipo];
        document.getElementById('modal_confirmar_limpiar_texto').textContent = textos[tipo];
        document.getElementById('input_confirmar_limpiar').value = '';
        document.getElementById('btn_confirmar_limpiar').disabled = true;

        new bootstrap.Modal(document.getElementById('modal_confirmar_limpiar')).show();
    }

    async function ejecutarLimpiar() {
        const confirmacion = document.getElementById('input_confirmar_limpiar').value;
        const btn = document.getElementById('btn_confirmar_limpiar');
        const urls = {
            documentos: @json(route('tenant.mantenimientos.configuracion.limpiarDocumentos')),
            todo: @json(route('tenant.mantenimientos.configuracion.limpiarTodo'))
        };

        try {
            btn.disabled = true;
            btn.innerHTML = '<div class="spinner-border spinner-border-sm text-white" role="status"></div> Eliminando...';

            const token = document.querySelector('input[name="_token"]').value;
            const formData = new FormData();
            formData.append('confirmacion', confirmacion);

            const response = await fetch(urls[tipoLimpiar], {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                body: formData
            });

            const res = await response.json();

            bootstrap.Modal.getInstance(document.getElementById('modal_confirmar_limpiar')).hide();

            if (res.success) {
                toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                if (res.logout) {
                    setTimeout(() => {
                        window.location.href = @json(route('module.logout'));
                    }, 3000);
                } else {
                    setTimeout(() => window.location.reload(), 1500);
                }
            } else {
                toastr.error(res.message, 'ERROR EN EL SERVIDOR');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN');
        } finally {
            btn.textContent = 'Sí, eliminar';
        }
    }
</script>
<script src="{{ asset('assets/js/utils.js') }}"></script>
@endsection
