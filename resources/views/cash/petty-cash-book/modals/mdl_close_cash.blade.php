<div class="modal fade" id="mdl_close_cash" tabindex="-1" aria-labelledby="mdl_close_cash_label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <i class="fa fa-cogs text-primary me-2"></i>
                <div>
                    <h5 class="modal-title mb-0" id="mdl_close_cash_label">Caja</h5>
                    <small class="text-muted">Cerrar Caja</small>
                </div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                @include('cash.petty-cash-book.forms.form_close_cash')
            </div>

            <div class="modal-footer d-flex justify-content-between align-items-center flex-wrap">
                <div class="text-warning small">
                    <i class="fa fa-exclamation-circle"></i>
                    Los campos marcados con asterisco (<label class="required"></label>) son obligatorios.
                </div>
                <div class="mt-sm-0 mt-2 text-end">
                    <button type="button" class="btn btn-primary btn-sm btnCloseCash">
                        <i class="fa fa-save"></i> Grabar
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">
                        <i class="fa fa-times"></i> Cancelar
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>


<script>
    const paramsMdlCloseCash = {
        id: null
    };

    async function openMdlCloseCash(cashBookId) {
        paramsMdlCloseCash.id = cashBookId;

        const data = await getConsolidatedCash(cashBookId);
        if (!data) return;
        paintConsolidatedCash(data);
        $('#mdl_close_cash').modal('show');
    }

    function eventsMdlCloseCash() {
        document.querySelector('.btnCloseCash').addEventListener('click', (e) => {
            closeCash(e.target);
        })
    }

    function paintConsolidatedCash(data) {

        const sales = data.report_sales;
        const expenses = data.report_expenses;
        const customer_accounts = data.report_customer_accounts;

        const pettyCashBook = data.petty_cash_book;
        document.querySelector('#consolidated_caja').textContent = pettyCashBook.petty_cash_name;
        document.querySelector('#consolidated_cajero').textContent = pettyCashBook.user_name;
        document.querySelector('#saldo_inicial_consolidated').textContent = formatSoles(pettyCashBook.initial_amount);
        document.querySelector('#monto_cierre_consolidated').textContent = formatSoles(data.amount_close);


        document.getElementById("total_sales_general").textContent = formatSoles(sales.total);
        document.getElementById("total_expenses_general").textContent = formatSoles(expenses.total);
        document.getElementById("total_customer_accounts_general").textContent = formatSoles(customer_accounts.total);

        const salesContainer = document.getElementById("sales_container");
        const expensesContainer = document.getElementById("expenses_container");
        const customerAccountsContainer = document.getElementById("customer_accounts_container");

        salesContainer.innerHTML = "";
        expensesContainer.innerHTML = "";

        sales.report.forEach(item => {
            const html = `
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="fw-semibold text-dark">
                        <i class="fa-solid fa-wallet text-primary me-1"></i>
                        ${item.payment_method_name}
                    </span>
                    <span class="fw-bold text-primary">${formatSoles(item.amount)}</span>
                </div>
            `;
            salesContainer.insertAdjacentHTML("beforeend", html);
        });

        expenses.report.forEach(item => {
            const html = `
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="fw-semibold text-dark">
                        <i class="fa-solid fa-money-bill-transfer text-danger me-1"></i>
                        ${item.payment_method_name}
                    </span>
                    <span class="fw-bold text-danger">${formatSoles(item.amount)}</span>
                </div>
            `;
            expensesContainer.insertAdjacentHTML("beforeend", html);
        });

        // Cuentas Cliente
        customer_accounts.report.forEach(item => {
            const html = `
                <div class="d-flex justify-content-between border-bottom pb-2 mb-2">
                    <span class="fw-semibold text-dark">
                        <i class="fa-solid fa-user text-primary me-1"></i>
                        ${item.payment_method_name}
                    </span>
                    <span class="fw-bold text-primary">${formatSoles(item.amount)}</span>
                </div>
            `;
            customerAccountsContainer.insertAdjacentHTML("beforeend", html);
        });

    }

    async function getConsolidatedCash(cashBookId) {
        try {
            mostrarAnimacion1();
            toastr.clear();
            const res = await axios.get(route('tenant.movimientos_caja.getConsolidated', {
                id: cashBookId
            }));

            if (res.data.success) {
                toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
                return res.data.consolidated;
            } else {
                return null;
                toastr.error(res.data.message);
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN OBTENER CAJA');
            return null;
        } finally {
            ocultarAnimacion1();
        }
    }

    function closeCash() {

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "Desea cerrar la caja?",
            message: `OPERACIÓN NO REVERSIBLE`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí!",
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                Swal.fire({
                    title: "Cerrando caja...",
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

                    const formData = new FormData();
                    formData.append('id', paramsMdlCloseCash.id);

                    const res = await axios.post(route('tenant.movimientos_caja.closePettyCash',
                            paramsMdlCloseCash.id),
                        formData);

                    if (res.data.success) {
                        toastr.success(res.data.message, 'OPERCIÓN COMPLETADA');
                        $('#mdl_close_cash').modal('hide');
                        dtCash.ajax.reload();
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
</script>
