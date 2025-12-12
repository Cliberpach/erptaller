<div class="modal fade" id="modal_detalle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title text-uppercase fw-bold">Detalle de Cuenta Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" name="cuenta_cliente_id" id="cuenta_cliente_id">
                @include('accounts.customer_accounts.forms.form_pagar')
            </div>

            <div class="modal-footer">
                <div class="col-md-6 text-end">
                    <button type="submit" class="btn btn-primary btn-sm" id="btn_guardar_detalle" form="frmDetalle">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cerrar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


<style>
    .imagen {
        width: 200px;
        height: 200px;
        border-radius: 10%;
    }
</style>


<script>
    const parametrosMdlPagar = {
        id: null
    };

    function loadFilePound() {

        const input = document.querySelector('#imagen');
        FilePond.create(input, {
            allowImagePreview: true,
            imagePreviewHeight: 120,
            imageCropAspectRatio: '1:1',
            styleLayout: 'compact',
            stylePanelAspectRatio: 0.5,
            storeAsFile: true,
            acceptedFileTypes: ['image/*'],
            labelFileTypeNotAllowed: 'Solo se permiten imágenes.',
        });

    }

    function eventsMdlPagar() {

        loadFilePound();

        document.querySelector('#frmDetalle').addEventListener('submit', (e) => {
            e.preventDefault();
            storePago(e.target);
        })

        $('#modal_detalle').on('hidden.bs.modal', function(e) {
            limpiarMdlPagar();
        });

    }

    async function openMdlPagar(cuentaId) {
        parametrosMdlPagar.id = cuentaId;

        mostrarAnimacion1();
        const data = await getCuentaCliente(cuentaId);
        if (!data) return;
        pintarCuentaCliente(data.cuenta);
        pintarDetallePago(data.detalle);
        const urlPdfOne = route('tenant.cuentas.cliente.pdfOne', {
            id: cuentaId
        });
        $("#btn-detalle").attr('href', urlPdfOne)
        $('#modal_detalle').modal('show');
        ocultarAnimacion1();
    }

    async function getCuentaCliente(cuentaId) {
        try {
            const res = await axios.get(route('tenant.cuentas.cliente.getCustomerAccount', {
                id: cuentaId
            }));
            if (!res.data.success) {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                return null;
            }
            return res.data.data;
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER CUENTA CLIENTE');
            return null;
        }
    }

    function pintarCuentaCliente(cuenta) {
        document.querySelector('#cliente').textContent = cuenta.customer_name;
        document.querySelector('#numero').textContent = cuenta.document_number;
        document.querySelector('#monto').textContent = formatSoles(cuenta.amount);
        document.querySelector('#saldo').textContent = formatSoles(cuenta.balance);
        document.querySelector('#estado').textContent = cuenta.status;
        document.querySelector('#type_document').textContent = `ORDEN DE TRABAJO`;
    }

    function pintarDetallePago(detalle) {
        const table = $(".dataTables-detalle").DataTable();
        table.clear().draw();

        const BASE_STORAGE_URL = @json(asset(''));

        detalle.forEach((value) => {
            let imagenHTML = '-';

            if (value.img_route) {
                const link_img = `${BASE_STORAGE_URL}${value.img_route}`;

                imagenHTML = `
                        <a href="${link_img}" target="_blank">
                            <img src="${link_img}"
                                style="width:80px; height:80px; object-fit:contain; border-radius:4px; border:1px solid #ddd;" />
                        </a>
                    `;
            }

            table.row.add([
                value.date,
                value.observation,
                formatSoles(value.total),
                imagenHTML
            ]).draw(false);
        });
    }

    function storePago(formPagar) {

        clearValidationErrors('msgError');

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea registrar el pago?",
            html: `Confirmar`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: "Registrando pago...",
                    text: "Por favor, espere",
                    icon: "info",
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    toastr.clear();

                    const formData = new FormData(formPagar);
                    formData.append('id', parametrosMdlPagar.id);
                    const res = await axios.post(route('tenant.cuentas.cliente.storePago'), formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                        $('#modal_detalle').modal('hide');
                        dtCuentasCliente.ajax.reload();
                    } else {
                        Swal.close();
                        toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    if (error.response) {
                        if (error.response.status === 422) {
                            const errors = error.response.data.errors;
                            paintValidationErrors(errors, 'error');
                            Swal.close();
                            toastr.error('Errores de validación encontrados.', 'ERROR DE VALIDACIÓN');
                        } else {
                            Swal.close();
                            toastr.error(error.response.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } else if (error.request) {
                        Swal.close();
                        toastr.error('No se pudo contactar al servidor. Revisa tu conexión a internet.',
                            'ERROR DE CONEXIÓN');
                    } else {
                        Swal.close();
                        toastr.error(error.message, 'ERROR DESCONOCIDO');
                    }
                } finally {
                    Swal.close();
                }

            } else if (result.dismiss === Swal.DismissReason.cancel) {
                swalWithBootstrapButtons.fire({
                    title: "Operación cancelada",
                    text: "No se realizaron acciones",
                    icon: "error"
                });
            }
        });
    }

    function limpiarImagen() {
        const inputImg = document.querySelector('#imagen');
        let pond = FilePond.find(inputImg);
        if (pond) {
            pond.removeFiles();
        }
    }

    async function changeModoPago(b) {

        //======= EFECTIVO ========
        if (b.value == 1) {
            $("#efectivo_venta").attr('readonly', false)
            $("#importe_venta").attr('readonly', true)
            $("#importe_venta").val(0.00)
            changeEfectivo()
        } else { //======= OTRO MÉT PAGO ========
            $("#efectivo_venta").attr('readonly', false)
            $("#importe_venta").attr('readonly', false)
            $("#efectivo_venta").val(0.00)
        }

        mostrarAnimacion1();
        toastr.clear();
        const cuentas = await getCuentasPorMetodoPago(b.value);
        if (!cuentas) return;
        pintarCuentas(cuentas);
        ocultarAnimacion1();
    }

    function pintarCuentas(cuentas) {
        window.cuentaSelect.clear();

        window.cuentaSelect.clearOptions();

        window.cuentaSelect.addOptions(cuentas);

        window.cuentaSelect.refreshOptions(false);

    }

    function changeEfectivo() {
        let efectivo = parseFloat($('#efectivo_venta').val());
        let importe = parseFloat($('#importe_venta').val());
        let suma = efectivo + importe;
        $('#cantidad').val(suma.toFixed(2))
    }

    function changeImporte() {
        let efectivo = parseFloat($('#efectivo_venta').val());
        let importe = parseFloat($('#importe_venta').val());
        let suma = efectivo + importe;
        $('#cantidad').val(suma.toFixed(2));
    }

    function tipoPago(tipoPago) {
        const tipo_pago = tipoPago.value;
        if (tipo_pago == "TODO") {
            const saldo = document.querySelector('#saldo').textContent;
            const modoPagoId = document.querySelector('#modo_pago').value;
            if (modoPagoId == 1) {
                document.querySelector('#efectivo_venta').value = saldo;
            } else {
                document.querySelector('#importe_venta').value = saldo;
            }
            document.querySelector('#cantidad').value = saldo;
        }
        if (tipo_pago == "A CUENTA") {
            document.querySelector('#efectivo_venta').value = 0;
            document.querySelector('#importe_venta').value = 0;
            document.querySelector('#cantidad').value = 0;
        }
    }

    function iniciarSelectsMdlPagar() {
        window.pagoSelect = new TomSelect("#pago", {
            placeholder: "SELECCIONAR",
            allowEmptyOption: true,
            create: false,
            maxOptions: null,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        window.modoPagoSelect = new TomSelect("#modo_pago", {
            placeholder: "SELECCIONAR",
            allowEmptyOption: false,
            create: false,
            maxOptions: null,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        window.cuentaSelect = new TomSelect("#cuenta", {
            placeholder: "SELECCIONAR",
            allowEmptyOption: false,
            create: false,
            maxOptions: null,
            valueField: 'id',
            labelField: 'text',
            searchField: ['text'],
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        // window.mododDespachoSelect = new TomSelect("#modo_despacho", {
        //     placeholder: "SELECCIONAR",
        //     allowEmptyOption: false,
        //     create: false,
        //     maxOptions: null,
        //     sortField: {
        //         field: "text",
        //         direction: "asc"
        //     }
        // });
    }

    async function getCuentasPorMetodoPago(metodoPagoId) {
        try {
            const res = await axios.get(route('tenant.utils.getListBankAccounts', {
                payment_method_id: metodoPagoId
            }));

            if (!res.data.success) {
                toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                return null;
            }
            toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            return res.data.bank_accounts;
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER CUENTAS POR MÉTODO DE PAGO');
            return null;
        }
    }

    function limpiarMdlPagar() {
        document.querySelector('#cliente').value = '';
        document.querySelector('#numero').value = '';
        document.querySelector('#monto').value = '';
        document.querySelector('#saldo').value = '';
        document.querySelector('#estado').value = '';
        document.querySelector('#observacion').value = '';
        document.querySelector('#nro_operacion').value = '';
        window.pagoSelect.setValue("A CUENTA");
        window.modoPagoSelect.setValue(3);
        limpiarImagen();
    }
</script>
