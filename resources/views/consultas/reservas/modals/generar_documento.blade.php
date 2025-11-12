<!-- Modal: Generar Documento de Pago desde Punto de Venta -->

<style>
    .modal-header-custom {
        color: white;
    }

    .modal-content {
        border: none;
        border-radius: 1rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        overflow: hidden;
    }

    .modal-body {
        background-color: #f9fafb;
    }

    .form-label {
        color: #374151;
    }

    .form-control,
    .form-select {
        border-radius: 0.75rem;
    }

    .list-group-item {
        border: none;
        background: #ffffff;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }

    .modal-footer {
        background-color: #f1f5f9;
    }

    .btn-primary {
        background-color: #2563eb;
        border: none;
    }

    .btn-primary:hover {
        background-color: #1d4ed8;
    }

    .btn-outline-secondary:hover {
        background-color: #e5e7eb;
    }

    @media (max-width: 576px) {
        .modal-dialog {
            margin: 1rem;
        }
    }
</style>

<div class="modal fade" id="mdlGenerateDoc" tabindex="-1" aria-labelledby="mdlGenerateDocLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header modal-header-custom px-4 py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center" id="mdlGenerateDocLabel">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Generar Documento de Pago
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Cerrar"></button>
            </div>

            <div class="modal-body px-4 py-4">
                @csrf
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-4">
                        <label for="tipoComprobante" class="form-label fw-semibold">Tipo de Comprobante</label>
                        <select class="form-select shadow-sm" id="tipoComprobante">
                            @foreach ($document_types as $document)
                                <option value="{{ $document->id }}">{{ $document->description }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-8">
                        <label for="documentInput" class="form-label fw-semibold" id="documentLabel">DNI</label>
                        <div class="input-group shadow-sm">
                            <input type="text" class="form-control" id="documentInput"
                                placeholder="Ingrese DNI o RUC" maxlength="8">
                            <button class="btn btn-outline-secondary" type="button" id="btnBuscarCliente"
                                title="Buscar cliente">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-12">
                        <label for="nombreCliente" class="form-label fw-semibold">Nombre / Razón Social</label>
                        <input type="text" class="form-control shadow-sm" id="nombreCliente" disabled
                            placeholder="Aquí se mostrará el nombre o razón social">
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <h6 class="fw-bold text-secondary mb-2">
                            <i class="fas fa-list-ul me-1"></i> Reservas seleccionadas
                        </h6>

                        <div class="table-responsive">
                            @include('consultas.reservas.tables.tbl_select_reservations')
                        </div>
                    </div>
                </div>
                <ul id="detalleCreditosSeleccionados" class="list-group mb-4"></ul>

                <div class="row text-end small text-muted mb-3">
                    <div class="col-6 offset-6">
                        <div>Op. Gravada: <span id="subtotalOperacion" class="text-dark fw-bold">00.00</span></div>
                        <div>IGV (18%): <span id="igvOperacion" class="text-dark fw-bold"> 00.00</span></div>
                    </div>
                </div>

                <div class="text-end fw-bold fs-5">
                    Total a pagar: <span class="text-success">S/ <span id="totalPagar">0.00</span></span>
                </div>
            </div>

            <div class="modal-footer d-flex justify-content-between px-4 py-3">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary rounded-pill px-4" id="btnConfirmarPago">
                    <i class="fas fa-check-circle me-1"></i> Generar Documento
                </button>
            </div>
        </div>
    </div>
</div>


<script>
    const parameters = {
        lstReservations: [],
        dtSeletReservations: null,
        amounts: {
            subtotal: 0,
            monto_igv: 0,
            total: 0
        },
        customer: {
            nro_document: null,
            name: null
        }
    };

    function openMdlDocument() {

        toastr.clear();
        if (parameters.lstReservations.length === 0) {
            toastr.error('EL LISTADO DE RESERVAS ESTÁ VACÍO');
            return;
        }

        //======== PINTAR TABLA RESERVATIONS SELECCIONADAS =========
        destroyDataTable(parameters.dtSeletReservations);
        clearTable('tbl-select-reservations');
        paintTblSelectReservations(parameters.lstReservations);
        parameters.dtSeletReservations = loadDataTableResponsive('tbl-select-reservations');

        //======== CALCULAR MONTOS ========
        calculateDocAmounts(parameters.lstReservations);
        paintAmounts(parameters.amounts);

        //========== SET DATOS CLIENTE =======
        setDataCustomer(parameters.lstReservations);

        $('#mdlGenerateDoc').modal('show');

    }

    function eventsMdlDocument() {

        //===== SELECT TIPO COMPROBANTE ========
        document.querySelector('#tipoComprobante').addEventListener('change', () => {
            const documentInput = document.getElementById('documentInput');
            if (tipoComprobante.value == 3) {
                documentLabel.textContent = 'DNI';
                documentInput.placeholder = 'Ingrese DNI';
                documentInput.maxLength = 8;
            } else {
                documentLabel.textContent = 'RUC';
                documentInput.placeholder = 'Ingrese RUC';
                documentInput.maxLength = 11;
            }
            documentInput.value = '';
            nombreCliente.value = '';
        });

        //====== BTN GENERAR DOC =====
        document.querySelector('#btnConfirmarPago').addEventListener('click', () => {
            toastr.clear();
            const validation = validationGenerateDocument(parameters);
            if (!validation) return;
            generateDocument();
        })

        //======= CIERRE MODAL GENERATE DOC ========
        $('#mdlGenerateDoc').on('hidden.bs.modal', function(e) {
            $('#tipoComprobante').val(3).trigger('change');
            document.querySelector('#documentInput').value = '';
            document.querySelector('#nombreCliente').value = '';
        });

        //======= BTN BUSCAR DOCUMENTO CLIENTE =====
        document.getElementById('btnBuscarCliente').addEventListener('click', () => {
            const tipoComprobante   = document.getElementById('tipoComprobante').value;
            const nroDocumento      = document.getElementById('documentInput').value;
            buscarCliente(tipoComprobante, nroDocumento);
        });
    }

    function selectReservation(reservationId, check) {

        toastr.clear();

        //====== OBTENER RESERVATION DEL DATATABLE ========
        const row = getRowById(window.dtReservations, reservationId);

        //======= AGREGAR RESERVACION AL LISTADO ========
        const validation = validationSelectReservation(parameters.lstReservations, reservationId, row);
        if (!validation) return;

        //======= AGREGAR RESERVATION ======
        if (check) {
            const validationAdd = validationAddReservation(parameters.lstReservations, reservationId);
            if (!validationAdd) return;
            addReservation(row);
        }

        //======= QUITAR RESERVATION =======
        if (!check) {
            deleteReservation(parameters.lstReservations, reservationId);
        }

    }

    function validationSelectReservation(lst, reservationId, row) {
        let validation = true;

        //======= VALIDAR ID RESERVATION =========
        if (!reservationId) {
            validation = false;
            toastr.error('FALTA EL PARÁMETRO ID RESERVACIÓN');
        }

        //======= VALIDAR RESERVATION EN DATATABLE ==========
        if (!row) {
            validation = false;
            toastr.error('NO EXISTE LA RESERVACIÓN EN LA TABLA');
        }

        return validation;
    }

    function validationAddReservation(lst, reservationId) {
        let validation = true;
        const existe = lst.findIndex(r => r.id == reservationId);
        if (existe !== -1) {
            toastr.error('LA RESERVA YA FUE AGREGADA');
            validation = false;
        }
        return validation;
    }

    function addReservation(row) {
        parameters.lstReservations.push(row);
        toastr.success('RESERVA AGREGADA');
    }

    function deleteReservation(lst, reservationId) {
        parameters.lstReservations = lst.filter(r => r.id != reservationId);
        toastr.error('RESERVA ELIMINADA');
    }

    function paintTblSelectReservations(lst) {
        const tbody = document.querySelector('#tbl-select-reservations tbody');
        let filas = '';

        lst.forEach((r) => {
            filas += `
                        <tr>
                            <td>${r.customer_name}</td>
                            <td>${r.customer_type_document_name}</td>
                            <td>${r.customer_document_number}</td>
                            <td>${r.customer_phone}</td>
                            <td>${r.field_name}</td>
                            <td>${r.schedule}</td>
                            <td>${r.date}</td>
                            <td>${r.total_hours}</td>
                            <td>${r.ball}</td>
                            <td>${r.vest}</td>
                            <td>${r.dni}</td>
                            <td>${r.amount}</td>
                            <td>${r.payment_status}</td>
                            <td>${r.facturado}</td>
                        </tr>
                    `;
        })

        tbody.innerHTML = filas;
    }

    const calculateDocAmounts = (lstItems) => {
        const percentageIgv = @json($company->igv);
        let subtotal = 0;
        let total = 0;
        let monto_igv = 0;


        lstItems.forEach((item) => {
            total += (parseFloat(item.amount));
        })

        subtotal = total / (1 + (percentageIgv / 100));
        monto_igv = total - subtotal;

        parameters.amounts.subtotal = subtotal;
        parameters.amounts.monto_igv = monto_igv;
        parameters.amounts.total = total;

    }

    function paintAmounts(amounts) {
        console.log(amounts);
        document.querySelector('#subtotalOperacion').textContent = formatMoney(amounts.subtotal);
        document.querySelector('#igvOperacion').textContent = formatMoney(amounts.monto_igv);
        document.querySelector('#totalPagar').textContent = formatMoney(amounts.total);
    }

    function setDataCustomer(lst) {
        if (lst.length > 0) {
            const firsReservation = lst[0];
            parameters.customer.nro_document = firsReservation.customer_document_number;
            parameters.customer.name = firsReservation.customer_name;

            document.querySelector('#nombreCliente').value = parameters.customer.name;
        }
    }

    function validationGenerateDocument(parameters) {
        let validation = true;
        if (parameters.lstReservations.length === 0) {
            validation = false;
            toastr.error('LA LISTA DE RESERVAS ESTÁ VACÍA');
        }
        return validation;
    }

    function getDataMdlDocument(lst) {
        const formData = new FormData();
        const customerOptional = {
            nro_document: document.querySelector('#documentInput').value,
            type_document: document.querySelector('#tipoComprobante').value
        }
        formData.append('lstReservations', JSON.stringify(lst));
        formData.append('nro_document', customerOptional.nro_document);
        formData.append('type_document', customerOptional.type_document);
        formData.append('nombreCliente', document.querySelector('#nombreCliente').value)
        return formData;
    }

    function generateDocument() {

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            title: "DESEA GENERAR EL DOCUMENTO DE VENTA?",
            html: `
                <div style="text-align: center;">
                    📌 <strong>Cliente:</strong> ${parameters.customer.name}<br>
                    🧾 <strong>Documento:</strong> ${parameters.customer.nro_document}
                </div>
            `,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nueva categoría...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {

                    clearValidationErrors('msgError');
                    const token = document.querySelector('input[name="_token"]').value;
                    const formData = getDataMdlDocument(parameters.lstReservations);


                    const url = @json(route('tenant.consultas.reservas.generar-documento'));

                    const response = await fetch(url, {
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
                        window.open(res.url_pdf, '_blank');
                        window.dtReservations.ajax.reload();
                        $('#mdlGenerateDoc').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    }


                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN GENERAR DOCUMENTO DE VENTA');
                } finally {
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

    function resetParameters() {
        $('#tipoComprobante').val(3).trigger('change');
        document.querySelector('#documentInput').value = '';
        document.querySelector('#nombreCliente').value = '';

        destroyDataTable(parameters.dtSeletReservations);
        clearTable('tbl-select-reservations');

        parameters.amounts = {
            subtotal: 0,
            monto_igv: 0,
            total: 0
        };

        parameters.lstReservations = [];
    }

    async function buscarCliente(tipoComprobante, nroDocumento) {
        const tipo              = tipoComprobante;
        const numeroDocumento   = nroDocumento;
        const nombreCliente     = document.getElementById('nombreCliente');

        if (!numeroDocumento) {
            Swal.fire({
                icon: 'warning',
                title: 'Campo vacío',
                text: 'Por favor, ingrese un número de documento.'
            });
            return;
        }

        if ((tipo === '3' && numeroDocumento.length !== 8) || (tipo === '1' && numeroDocumento.length !== 11)) {
            Swal.fire({
                icon: 'warning',
                title: tipo === '3' ? 'DNI inválido' : 'RUC inválido',
                text: tipo === '3' ? 'Debe ingresar un DNI válido de 8 dígitos.' :
                    'Debe ingresar un RUC válido de 11 dígitos.'
            });
            return;
        }

        mostrarAnimacion1();

        try {
            let urlLocal = tipo === '3' ? `/api/customers/${numeroDocumento}` : `/api/customers/ruc/${numeroDocumento}`;

            const localResponse = await fetch(urlLocal);
            const localData     = await localResponse.json();

            //========== BUSCAR EN LANDLORD CUSTOMERS ========
            if (localData.data || localData.razon_social) {
                nombreCliente.value = localData.data?.name || localData.razon_social;

                //======== GUARDAR CUSTOMER PARAMETER =========
                parameters.customer.name            = nombreCliente.value;
                parameters.customers.nro_document   = numeroDocumento;

                ocultarAnimacion1();
                return;
            }

            //========= BUSCAR RENIEC =========
            const urlExterno = tipo === '3' ? `/landlord/dni/${numeroDocumento}` : `/landlord/ruc/${numeroDocumento}`;

            const externaResponse = await fetch(urlExterno, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (!externaResponse.ok) throw new Error('Error HTTP');

            const externaData = await externaResponse.json();

            if (!externaData.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'No encontrado',
                    text: tipo === '3' ?
                        'DNI inválido o no existe en RENIEC.' : 'RUC inválido o no existe en SUNAT.'
                });
            } else {
                nombreCliente.value = tipo === '3' ? externaData.data.nombre_completo : externaData.data.nombre_o_razon_social;
                //======== GUARDAR CUSTOMER PARAMETER =========
                parameters.customer.name            = nombreCliente.value;
                parameters.customer.nro_document    = numeroDocumento;
            }
        } catch (error) {
            console.error('Error en la consulta:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al consultar. Intenta nuevamente más tarde.'
            });
        } finally {
            ocultarAnimacion1();
        }
    }
</script>
