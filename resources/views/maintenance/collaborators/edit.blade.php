@extends('layouts.template')
@section('title')
    EDITAR COLABORADOR
@endsection

@section('content')
    @include('maintenance.positions.modals.modal_create_cargo')
    @include('maintenance.positions.modals.modal_edit_cargo')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h6>Datos del Colaborador<i class="fa-solid fa-user"></i></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    @include('maintenance.collaborators.forms.form_edit_colaborador')
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;">Los campos con * son obligatorios</span>

            <div style="display:flex;">
                <button class="btn btn-danger btnVolver" style="margin-right:5px;" type="button">
                    <i class="fa-solid fa-door-open"></i> VOLVER
                </button>
                <button class="btn btn-primary" type="submit" form="formActualizarColaborador">
                    <i class="fa-solid fa-floppy-disk"></i> ACTUALIZAR
                </button>
            </div>
        </div>
    </div>

    <!-- end card -->
@endsection

<style>
    .swal2-container {
        z-index: 9999999;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        iniciarSelect2();
        events();
    })

    function events() {

        document.querySelector('#formActualizarColaborador').addEventListener('submit', (e) => {
            e.preventDefault();
            actualizarColaborador();
        })

        document.addEventListener('click', (e) => {
            if (e.target.closest('.btnVolver')) {
                const rutaIndex = '{{ route('tenant.mantenimientos.colaboradores.index') }}';
                window.location.href = rutaIndex;
            }
        })

        //======= CONSULTAR API DOCUMENTO DNI ========
        document.querySelector('#btn_consultar_documento').addEventListener('click', () => {
            const dni = document.querySelector('#document_number').value;
            const tipo_documento = document.querySelector('#document_type').value;
            toastr.clear();

            if (tipo_documento != 39) {
                toastr.error('SOLO SE PUEDE CONSULTAR TIPO DE DOCUMENTO DNI');
                return;
            }

            if (dni.length != 8) {
                toastr.error('NRO DE DNI DEBE CONTAR CON 8 DÍGITOS');
                return;
            }

            consultarDocumento(dni);

        })

        //======== PERMITIR SOLO NROS EN HORAS SEMANA =======
        document.querySelector('#work_days').addEventListener('input', (e) => {
            const input = e.target;
            const validNumberPattern = /^[1-9]\d*$/;
            input.value = input.value.replace(/(?!^)(^|\D+|(?<=\D)\d*|\D*$)/g, '');

            if (!validNumberPattern.test(input.value) && input.value !== '') {
                input.value = '';
            }
        })

        //======== PERMITIR SOLO NROS EN HORAS SEMANA =======
        document.querySelector('#rest_days').addEventListener('input', (e) => {
            const input = e.target;
            const validNumberPattern = /^[1-9]\d*$/;
            input.value = input.value.replace(/(?!^)(^|\D+|(?<=\D)\d*|\D*$)/g, '');

            if (!validNumberPattern.test(input.value) && input.value !== '') {
                input.value = '';
            }
        })

        //========= PERMITIR CONTENIDO VALIDO DE DINERO =====
        document.querySelector('#monthly_salary').addEventListener('input', (e) => {
            const input = e.target;

            // Reemplaza cualquier carácter que no sea un dígito o un punto decimal
            let value = input.value.replace(/[^0-9.]/g, '');

            // Asegúrate de que el punto decimal no esté al inicio
            if (value.startsWith('.')) {
                value = value.slice(1);
            }

            // Permite solo un punto decimal y limita a dos decimales
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }

            if (parts.length === 2) {
                parts[1] = parts[1].slice(0, 2); // Limita a dos decimales
                value = parts.join('.');
            }

            // Actualiza el valor del input
            input.value = value;
        })

        //========== PERMITIR SOLO FORMATO DE CELULAR O TELEFONO ======
        document.querySelector('#phone').addEventListener('input', (e) => {
            const input = e.target;
            const maxLength = 20;

            // Expresión regular para validar números de teléfono internacionales
            const validPattern = /^\+?[0-9]*$/;

            // Reemplaza cualquier carácter que no sea un dígito o "+"
            let value = input.value.replace(/[^0-9+]/g, '');

            // Asegúrate de que el símbolo '+' esté al principio
            if (value.startsWith('+')) {
                value = '+' + value.slice(1).replace(/^\+/, '');
            } else {
                value = value.replace(/^\+/, '');
            }

            // Limita el valor a 20 caracteres
            if (value.length > maxLength) {
                value = value.slice(0, maxLength);
            }

            // Actualiza el valor del input
            input.value = value;
        })

        //===== PERMITIR SOLO NUMEROS ========
        document.querySelector('#document_number').addEventListener('input', (e) => {
            const input = e.target;

            input.value = input.value.replace(/\D/g, '');
        });
    }

    function iniciarSelect2() {
        $('.select2_form').select2({
            theme: "bootstrap-5",
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
        });
    }

    function actualizarColaborador() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA ACTUALIZAR EL COLABORADOR?",
            text: "Se actualizaran los datos del colaborador!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {
                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formActualizarColaborador = document.querySelector('#formActualizarColaborador');
                const formData = new FormData(formActualizarColaborador);

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando colaborador...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    const id = @json($colaborador->id);
                    let urlUpdateColaborador =
                        `{{ route('tenant.mantenimientos.colaboradores.update', ['id' => ':id']) }}`;
                    urlUpdateColaborador = urlUpdateColaborador.replace(':id', id);

                    const response = await fetch(urlUpdateColaborador, {
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
                        const colaborador_index = @json(route('tenant.mantenimientos.colaboradores.index'));
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = colaborador_index;
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR COLABORADOR');
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

    //======== CHANGE TIPO DOCUMENTO ======
    function changeTipoDoc(params) {
        const tipo_documento = document.querySelector('#document_type').value;
        const inputNroDoc = document.querySelector('#document_number');
        const btnConsultarDocumento = document.querySelector('#btn_consultar_documento');

        //======== DNI =======
        if (tipo_documento == 39) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 8;
            btnConsultarDocumento.disabled = false;
        }

        //====== CARNET EXTRANJERÍA =====
        if (tipo_documento == 41) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 20;
            btnConsultarDocumento.disabled = true;
        }
    }

    //======= CONSULTAR DOCUMENTO IDENTIDAD =====
    async function consultarDocumento(dni) {
        mostrarAnimacion1();
        try {
            const token = document.querySelector('input[name="_token"]').value;
            const urlApiDni = `/grifo_colaboradores/consultarDni/${encodeURIComponent(dni)}`;

            const response = await fetch(urlApiDni, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                },
            });

            const res = await response.json();

            if (res.success) {

                if (!res.data.success) {
                    toastr.error(res.data.message);
                    return;
                }

                setDatosDni(res.data.data);
                toastr.info(res.message);
            } else {
                toastr.error(res.message, 'ERROR EN EL SERVIDOR AL CONSULTAR DNI');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN CONSULTAR DNI');
        } finally {
            ocultarAnimacion1();
        }
    }

    function setDatosDni(data) {
        const nombre_completo = `${data.nombres} ${data.apellido_paterno} ${data.apellido_materno}`;
        const direccion = data.direccion;

        document.querySelector('#nombre').value = nombre_completo;
        document.querySelector('#direccion').value = direccion;
    }
</script>
