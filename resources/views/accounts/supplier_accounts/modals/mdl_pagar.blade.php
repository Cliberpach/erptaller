<div class="modal fade" id="mdlPagar" tabindex="-1" aria-labelledby="modal_detalleLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" style="max-width: 80%;margin-right:auto;margin-left:auto;">
        <div class="modal-content animated bounceInRight">
            <div class="modal-header">
                <h5 class="modal-title text-uppercase font-weight-bold" id="modal_detalleLabel">Detalle de Cuenta
                    Proveedor</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="cuenta_proveedor_id" id="cuenta_proveedor_id">
                @include('accounts.supplier_accounts.lists.lst_pay')
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary btn-sm" id="btn_guardar_detalle" form="formPagar"><i
                        class="fa fa-save"></i> Guardar</button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal"><i class="fa fa-times"></i>
                    Cancelar</button>
            </div>
        </div>
    </div>
</div>

<script>
    const parametros = {
        cuentaProveedorId: null
    };
    let dtPagos = null;

    function eventsMdlPagar() {

        loadFilePound();
        loadSelectsMdlPagar();
        eventsSelectsMdlPagar();

        dtPagos = loadDataTableSimple('tbl_detalle_pago');
        $('#metodo_pago').val(1).trigger('change');

        document.querySelector('#formPagar').addEventListener('submit', (e) => {
            e.preventDefault();
            registrarPago(e.target);
        })

        //====== CIERRE MODAL ======
        $('#mdlPagar').on('hidden.bs.modal', function() {
            limpiarMdlPagar();
        });

        document.addEventListener('click', (e) => {
            //======== LIMPIAR IMAGEN =======
            if (e.target.classList.contains('btnSetImageDefault')) {
                const inputImgPreview = document.querySelector('#img_vista_previa');
                inputImgPreview.src = @json(asset('img/img_default.png'));

                const inputCargarImg = document.querySelector('#img_pago');
                inputCargarImg.value = '';
            }
        })

        document.querySelector('#img_pago').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            if (file) {

                reader.onload = function(e) {
                    document.getElementById('img_vista_previa').src = e.target.result;
                };

                reader.readAsDataURL(file);
            } else {
                document.getElementById('img_vista_previa').src = @json(asset('img/img_default.png'));
            }
        });

    }

    function loadSelectsMdlPagar() {
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

        window.metodoPagoSelect = new TomSelect("#metodo_pago", {
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

    }

    function eventsSelectsMdlPagar() {
        window.metodoPagoSelect.on('change', function(value) {
            changePaymentMethod(value);
        });
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

    function loadFilePound() {

        const input = document.querySelector('#img_pago');
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

    async function openMdlPagar(proveedorId) {

        parametros.cuentaProveedorId = proveedorId;

        const resGetCobranza = await getCobranza(proveedorId);
        if (!resGetCobranza) {
            return;
        }

        if (resGetCobranza.success) {
            const cobranza = resGetCobranza.data.cuenta;
            const detalle = resGetCobranza.data.detalle;

            pintarCobranza(cobranza);

            destroyDataTable(dtPagos);
            clearTable("tbl_detalle_pago");
            pintarPagos(detalle);
            dtPagos = loadDataTableSimple('tbl_detalle_pago');

            toastr.success(resGetCobranza.message, 'OPERACIÓN COMPLETADA');
            $('#mdlPagar').modal('show');
        } else {
            toastr.error(resGetCobranza.message, 'ERROR EN EL SERVIDOR');
        }
    }

    async function getCobranza(cuentaId) {

        toastr.clear();
        const token = document.querySelector('input[name="_token"]').value;
        let url = `{{ route('tenant.cuentas.proveedor.getSupplierAccount', ['id' => ':id']) }}`;
        url = url.replace(':id', cuentaId);

        try {
            mostrarAnimacion1();
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                }
            });

            const res = await response.json();

            return res;

        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER CUENTA PROVEEDOR');
            return null;
        } finally {
            Swal.close();
            ocultarAnimacion1();
        }

    }

    function pintarCobranza(cobranza) {
        document.querySelector('#cliente').textContent = cobranza.supplier_name;
        document.querySelector('#documento').textContent = cobranza.document_number;
        document.querySelector('#monto').textContent = formatSoles(cobranza.amount);
        document.querySelector('#saldo').textContent = formatSoles(cobranza.balance);

        const estado = document.querySelector('#estado');
        estado.textContent = cobranza.status;

        estado.classList.remove('text-success', 'text-danger', 'text-warning');

        if (cobranza.status === 'PAGADO') {
            estado.classList.add('text-success');
        } else if (cobranza.status === 'PENDIENTE') {
            estado.classList.add('text-warning');
        } else {
            estado.classList.add('text-danger');
        }
    }

    function pintarPagos(detalle) {

        let filas = ``;
        const tbody = document.querySelector('#tbl_detalle_pago tbody');

        const asset = @json(asset(''));

        detalle.forEach((d) => {
            filas += `<tr>
                            <td style="text-align:start;">${d.date}</td>
                            <td style="text-align:start;">${d.observation}</td>
                            <td style="text-align:end;">${formatSoles(d.total)}</td>
                            <td style="text-align:center;">
                                <img class="imgShowLightBox"
                                style="width:60px;object-fit:contain;cursor: pointer;" src="${asset}${d.img_route}">
                                </img>
                            </td>
                        </tr>`;
        })

        tbody.innerHTML = filas;
    }

    async function changePaymentMethod(paymentMethodId) {

        //======= EFECTIVO ========
        if (paymentMethodId == 1) {
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
        const cuentas = await getCuentasPorMetodoPago(paymentMethodId);
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

    function changeEfectivo(b) {
        const efectivo = parseFloat($('#efectivo_venta').val());
        const importe = parseFloat($('#importe_venta').val());
        const suma = efectivo + importe;
        $('#cantidad').val(suma.toFixed(2))
    }

    function changeImporte(b) {
        const efectivo = parseFloat($('#efectivo_venta').val());
        const importe = parseFloat($('#importe_venta').val());
        const suma = efectivo + importe;
        $('#cantidad').val(suma.toFixed(2))
    }

    function registrarPago(formPago) {

        Swal.fire({
            title: "DESEA REGISTRAR EL PAGO?",
            text: "Operación no reversible!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                toastr.clear();
                clearValidationErrors('msgError');
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(formPago);
                formData.append('id', parametros.cuentaProveedorId);
                const url = @json(route('tenant.cuentas.proveedor.storePago'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando pago...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            toastr.error('ERRORES DE VALIDACIÓN');
                            paintValidationErrors(res.errors, 'error');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {
                        dtCobranzas.draw();
                        $('#mdlPagar').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR PAGO');
                } finally {
                    Swal.close();
                }


            } else if (result.dismiss === Swal.DismissReason.cancel) {
                Swal.fire({
                    title: "OPERACIÓN CANCELADA",
                    text: "NO SE REALIZARON ACCIONES",
                    icon: "error"
                });
            }
        });
    }

    function limpiarMdlPagar() {

        parametros.cuentaProveedorId = null;

        let currentDate = new Date().toISOString().split('T')[0];
        document.querySelector('#fecha').value = currentDate;

        document.querySelector('#efectivo_venta').value = 0;
        document.querySelector('#cantidad').value = 0;
        document.querySelector('#importe_venta').value = 0;

        document.querySelector('#img_vista_previa').src = @json(asset('img/img_default.png'));
        document.querySelector('#img_pago').value = '';

        $('#metodo_pago').val(1).trigger('change');
        $('#pago').val("A CUENTA").trigger('change');

        destruirDataTable(dtPagos);
        limpiarTabla('tbl_detalle_pago');
        dtPagos = loadDataTableSimple('tbl_detalle_pago');
    }
</script>
