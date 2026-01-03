@extends('layouts.template')
@section('title')
    EDITAR CLIENTE
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">EDITAR CLIENTE</h4>
            <div class="d-flex flex-wrap gap-2">

            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-12">
                    @include('sales.customers.forms.form_edit')
                </div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row">
                <div class="col-12 d-flex justify-content-end">

                    <!-- BOTÓN VOLVER -->
                    <button type="button" class="btn btn-danger me-1" onclick="redirect('tenant.ventas.cliente')">
                        <i class="fas fa-arrow-left"></i> VOLVER
                    </button>

                    <!-- BOTÓN REGISTRAR -->
                    <button class="btn btn-primary" type="submit" form="formActualizarCliente">
                        <i class="fa-solid fa-floppy-disk"></i> ACTUALIZAR
                    </button>

                </div>

            </div>
        </div>
    </div>
@endsection


<script>
    document.addEventListener('DOMContentLoaded', () => {
        iniciarSelect2();
        fijarUbigeo();
        events();
        configuracionInicial();
    })

    function events() {

        //===== CHECK PERMITIR VENTAS AL CRÉDITO =======
        /*document.querySelector('#control_credito').addEventListener('change', (e) => {
            const marcado = e.target.checked;
            const inputLimite = document.querySelector('#limite_credito');
            if (marcado) {
                inputLimite.readOnly = false;
                inputLimite.classList.add('colorReadOnly');
            } else {
                inputLimite.readOnly = true;
                inputLimite.classList.remove('colorReadOnly');
                inputLimite.value = 0;
            }
        })*/

        //======= CONSULTAR API DOCUMENTO DNI ========
        document.querySelector('#btn_consultar_documento').addEventListener('click', () => {

            const nro_documento = document.querySelector('#nro_document').value;
            const tipo_documento = document.querySelector('#type_identity_document').value;
            toastr.clear();

            if (tipo_documento != 1 && tipo_documento != 3) {
                toastr.error('SOLO SE PUEDE CONSULTAR DNI Y RUC');
                return;
            }

            if (tipo_documento == 1 && nro_documento.length != 8) {
                toastr.error('NRO DE DNI DEBE CONTAR CON 8 DÍGITOS');
                return;
            }

            if (tipo_documento == 3 && nro_documento.length != 11) {
                toastr.error('NRO DE RUC DEBE CONTAR CON 11 DÍGITOS');
                return;
            }

            consultarDocumento(tipo_documento, nro_documento);

        })

        document.querySelector('#formActualizarCliente').addEventListener('submit', (e) => {
            e.preventDefault();
            update();
        })

        document.addEventListener('click', (e) => {
            if (e.target.closest('.btnVolver')) {
                const rutaIndex = '{{ route('tenant.ventas.cliente') }}';
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

    function configuracionInicial() {
        /*
            const controlCredito = @json($customer->control_credito) == 1 ? true : false;
            const chkPermitirCredito = document.querySelector('#control_credito');
            chkPermitirCredito.checked = controlCredito;
            chkPermitirCredito.dispatchEvent(new Event('change'));
        */
    }

    function update() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA ACTUALIZAR EL CLIENTE?",
            text: "Se creará un nuevo cliente!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, ACTUALIZAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                clearValidationErrors('msgError');

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Actualizando cliente...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    const id = @json($customer->id);
                    const token = document.querySelector('input[name="_token"]').value;
                    const formupdate = document.querySelector('#formActualizarCliente');
                    const formData = new FormData(formupdate);

                    let url = `{{ route('tenant.ventas.cliente.update', ['id' => ':id']) }}`;
                    url = url.replace(':id', id);

                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'X-HTTP-Method-Override': 'PUT'
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
                        const cliente_index = @json(route('tenant.ventas.cliente'));
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        window.location.href = cliente_index;
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN ACTUALIZAR CLIENTE');
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
        const tipo_documento = document.querySelector('#type_identity_document').value;
        const inputNroDoc = document.querySelector('#nro_document');
        const btnConsultarDocumento = document.querySelector('#btn_consultar_documento');

        //======== DNI =======
        if (tipo_documento == 1) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 8;
            btnConsultarDocumento.disabled = false;
            inputNroDoc.classList.add('inputEnteroPositivo');
        }

        //======== RUC =======
        if (tipo_documento == 3) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 11;
            btnConsultarDocumento.disabled = false;
            inputNroDoc.classList.add('inputEnteroPositivo');
        }

        //====== CARNET EXTRANJERÍA =====
        if (tipo_documento == 2 || (tipo_documento != 1 && tipo_documento != 3)) {
            inputNroDoc.value = '';
            inputNroDoc.readOnly = false;
            inputNroDoc.maxLength = 20;
            btnConsultarDocumento.disabled = true;
            inputNroDoc.classList.remove('inputEnteroPositivo');
        }
    }

    //======= CONSULTAR DOCUMENTO IDENTIDAD =====
    async function consultarDocumento(tipo_documento, nro_documento) {
        mostrarAnimacion1();
        try {
            const token = document.querySelector('input[name="_token"]').value;
            const baseUrlCliente = @json(route('tenant.ventas.cliente.consult_document'));

            const url =
                `{{ route('tenant.ventas.cliente.consult_document') }}?type_identity_document=${encodeURIComponent(tipo_documento)}&nro_document=${encodeURIComponent(nro_documento)}`;

            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                },
            });

            const res = await response.json();

            if (res.success) {

                if (!res.success) {
                    toastr.error(res.data.message);
                    return;
                }
                if (tipo_documento == 1) {
                    setDatosDni(res.data);
                }
                if (tipo_documento == 3) {
                    setDatosRuc(res.data);
                }

                toastr.info('OPERACIÓN COMPLETADA', res.message);
            } else {
                toastr.error(res.message, 'ERROR EN EL SERVIDOR AL CONSULTAR DOCUMENTO');
            }

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN CONSULTAR DOCUMENTO');
        } finally {
            ocultarAnimacion1();
        }
    }

    /*
ubigeo:
[
  "01",
  "0101",
  "010101"
]
*/
    function setDatosRuc(data) {
        const nombre_o_razon_social = `${data.nombre_o_razon_social}`;
        const direccion_completa = data.direccion_completa;
        const ubigeo = data.ubigeo;

        document.querySelector('#nombre').value = nombre_o_razon_social;
        document.querySelector('#direccion').value = direccion_completa;

        //======= COLOCANDO UBIGEO =======
        let departamento_ubigeo = parseInt(ubigeo[0]);
        let provincia_ubigeo = parseInt(ubigeo[1]);
        let distrito_ubigeo = parseInt(ubigeo[2]);


        if (!isNaN(departamento_ubigeo)) {
            $('#departamento').val(departamento_ubigeo).trigger('change');
        }

        if (!isNaN(provincia_ubigeo)) {
            $('#provincia').val(provincia_ubigeo).trigger('change');
        }

        if (!isNaN(distrito_ubigeo)) {
            $('#distrito').val(distrito_ubigeo).trigger('change');
        }

    }

    function setDatosDni(data) {
        const nombre_completo = `${data.nombres} ${data.apellido_paterno} ${data.apellido_materno}`;
        const direccion = data.direccion;

        document.querySelector('#name').value = nombre_completo;
        document.querySelector('#address').value = direccion;
    }

    function cambiarDepartamento(selectDepartamento) {

        let departamento_id = selectDepartamento.value;
        const lstProvincias = @json($provinces);
        const lstDistritos = @json($districts);

        let lstProvinciasFiltradas = [];

        if (departamento_id) {

            departamento_id = String(departamento_id).padStart(2, '0');

            lstProvinciasFiltradas = lstProvincias.filter((provincia) => {
                return provincia.department_id == departamento_id;
            })

            $('#province').empty().trigger('change');

            lstProvinciasFiltradas.forEach((provincia) => {
                $('#province').append(new Option(provincia.name, provincia.id, false, false));
            })

            $('#province').select2({
                theme: "bootstrap-5",
                placeholder: 'Seleccione una provincia',
                width: '100%'
            });

            $('#province').trigger('change');
        }
    }

    function cambiarProvincia(selectProvincia) {

        let provincia_id = selectProvincia.value;
        const lstDistritos = @json($districts);

        let lstDistritosFiltrados = [];

        if (provincia_id) {
            provincia_id = String(provincia_id).padStart(4, '0');

            lstDistritosFiltrados = lstDistritos.filter((distrito) => {
                return distrito.province_id == provincia_id;
            })

            $('#district').empty().trigger('change');

            lstDistritosFiltrados.forEach((distrito) => {
                $('#district').append(new Option(distrito.name, distrito.id, false, false));
            })

            $('#district').select2({
                theme: "bootstrap-5",
                placeholder: 'Seleccione un distrito',
                width: '100%'
            });
        }

    }

    function fijarUbigeo() {
        const cliente = @json($customer);
        if (cliente.department_id) {
            const departamento_id = String(parseInt(cliente.department_id));
            $('#department').val(departamento_id).trigger('change');
        }
        if (cliente.province_id) {
            const provincia_id = String(parseInt(cliente.province_id));
            $('#province').val(provincia_id).trigger('change');
        }
        if (cliente.district_id) {
            const distrito_id = String(parseInt(cliente.district_id));
            $('#district').val(distrito_id).trigger('change');
        }
    }
</script>
