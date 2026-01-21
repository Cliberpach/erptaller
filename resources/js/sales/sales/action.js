import Swal from "sweetalert2";
import { routes } from "./routes";
import { amounts, app, debounceTimer, dtProducts, lstPays, lstSale, setDebounceTimer } from "./states";
import { paintLstPays, paintProductAmount, paintTableAmounts, paintTableSaleDetail } from "./ui";
import { validateStock } from "./fetch";
import { actionChangeClient } from "../../utils/action";

export function actionAmountPay(e) {
    const indexPay = e.target.getAttribute('data-index');
    const amount_pay = e.target.value;

    lstPays[indexPay].amount = amount_pay;
}


export function actionAddPay() {
    toastr.clear();
    if (lstPays.length < 2) {

        lstPays.push({
            method_pay: null,
            amount: 0
        });
        paintLstPays(lstPays);

    } else {
        toastr.error('MÁXIMO DE PAGOS PERMITIDOS 2!!!');
    }
}

export function actionCantProduct(e) {

    clearTimeout(debounceTimer);

    mostrarAnimacion1();
    toastr.clear();

    e.target.blur();
    const product_id = e.target.getAttribute('data-id');
    let cant = e.target.value;

    //========= VALIDANDO CANTIDAD =======
    //========= NO PERMITIR 0 ; COLOCAR 1 POR DEFECTO ======
    if (isNaN(parseFloat(cant))) {
        e.target.focus();
        ocultarAnimacion1();
        return;
    }

    if (!isNaN(parseFloat(cant)) && cant <= 0) {
        cant = 1;
        e.target.value = cant;
    }

    e.target.focus();
    ocultarAnimacion1();

    const _debounceTimer = setTimeout(async () => {
        mostrarAnimacion1();
        const product = {
            product_id,
            cant
        };
        let finalQuantity = cant;

        const resValidateStock = await validateStock(product);

        if (resValidateStock.success) {
            toastr.success(resValidateStock.message, 'OPERACIÓN COMPLETADA');
        } else {
            toastr.error(resValidateStock.message, 'ERROR EN EL SERVIDOR');
            e.target.value = resValidateStock.stock;
            finalQuantity = resValidateStock.stock;
        }

        const indexProduct = lstSale.findIndex((p) => {
            return p.id == product_id
        });
        lstSale[indexProduct].cant = finalQuantity;
        calculatePrices(lstSale);
        paintTableAmounts();

        //========= ACTUALIZANDO IMPORTE EN SALE TABLE DETAIL =======
        paintProductAmount(lstSale[indexProduct]);

        lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
        paintLstPays(lstPays);

        e.target.focus();
        ocultarAnimacion1();
    }, 1200);

    setDebounceTimer(_debounceTimer);

}

export function actionStore(e) {
    e.preventDefault();
    toastr.clear();
    const validation = validateSale();
    if (validation) {
        storeSale(e.target);
    }
}

function validateSale() {

    let validation = true;
    const conditionId = document.querySelector('#payment_condition_id').value;

    //======== VALIDANDO DETALLE DE VENTA =========
    if (lstSale.length === 0) {
        toastr.error('EL DETALLE DE LA VENTA ESTÁ VACÍO!!!');
        validation = false;
        return validation;
    }

    //======= VALIDANDO TIPO DE VENTA ===========
    const type_sale = document.querySelector('#type_sale').value;
    if (!type_sale) {
        toastr.error('DEBE SELECCIONAR UN TIPO DE COMPROBANTE!!!');
        validation = false;
    }


    //===== VALIDANDO CLIENTE =========
    const customer_id = document.querySelector('#customer_id');
    if (!customer_id.value) {
        toastr.error('DEBE SELECCIONAR UN CLIENTE!!!');
        validation = false;
    }

    if (conditionId == 1) {
        validation = validationLstPays();
    }

    return validation;
}

function validationLstPays() {
    let validation = true;
    const lstMethodPays = [];
    let paysRepeat = false;
    let payNoNumber = false;
    let payCero = false;
    let methodPayNull = false;
    let totalPay = 0;

    //======== VALIDANDO PAGOS ========
    if (lstPays.length === 0) {
        toastr.error('DEBE AGREGAR UN PAGO!!!');
        validation = false;
    }

    for (const pay of lstPays) {

        if (!pay.method_pay) {
            methodPayNull = true;
        }

        if (!lstMethodPays.includes(pay.method_pay)) {
            lstMethodPays.push(pay.method_pay);
        } else {
            paysRepeat = true;
        }

        if (isNaN(parseFloat(pay.amount))) {
            payNoNumber = true;
        }

        if (parseFloat(pay.amount) === 0) {
            payCero = true;
        }

        totalPay += parseFloat(pay.amount);
    }

    if (paysRepeat) {
        toastr.error('LOS MÉTODOS DE PAGO DEBEN SER DIFERENTES!!!');
        validation = false;
        return validation;
    }
    if (methodPayNull) {
        toastr.error('NO HA SELECCIONADO MÉTODO DE PAGO!!!');
        validation = false;
        return validation;
    }

    if (payNoNumber) {
        toastr.error('LOS PAGOS DEBEN SER NUMÉRICOS!!!');
        validation = false;
        return validation;
    }
    if (payCero) {
        toastr.error('LOS PAGOS DEBEN SER MAYOR A 0!!!');
        validation = false;
        return validation;
    }

    if (parseFloat(totalPay.toFixed(2)) !== parseFloat(amounts.total.toFixed(2))) {
        toastr.error('EL TOTAL DE VENTA NO COINCIDE CON EL TOTAL PAGO!!!');
        validation = false;
        return validation;
    }
    return validation;
}

function storeSale(formStore) {
    clearValidationErrors();

    Swal.fire({
        title: "DESEA REGISTRAR LA VENTA?",
        text: "Se generará un comprobante de venta!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, REGISTRAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
    }).then(async (result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Cargando...',
                html: 'Registrando venta...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const token = document.querySelector('input[name="_token"]').value;
                const formData = new FormData(formStore);
                const url = routes.store;

                formData.append('lstSale', JSON.stringify(lstSale));
                formData.append('lstPays', JSON.stringify(lstPays));
                formData.append('igv_percentage', app.companyIgv);

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
                        paintValidationErrors(res.errors);
                    }
                    Swal.close();
                    return;
                }

                if (res.success) {

                    toastr.success(res.message, 'OPERACIÓN COMPLETADA');

                    const url_open_pdf = routes.pdf( res.data.sale_id,0);
                    window.open(url_open_pdf, 'Comprobante SISCOM',
                        'location=1, status=1, scrollbars=1,width=900, height=600');

                    window.location.href = routes.index;

                } else {
                    toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR VENTA');
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

export function actionDeletePay(e) {
    const btn = e.target.closest('.btn_delete_pay');
    const indexPay = btn.dataset.index;

    lstPays.splice(indexPay, 1);
    paintLstPays(lstPays);
}

export function actionDeleteProduct(e) {
    toastr.clear();
    mostrarAnimacion1();
    const btnDeleteproduct = e.target.closest('.delete-product');
    const producto_id = btnDeleteproduct.getAttribute('data-id');

    const indexProductExists = lstSale.findIndex((p) => {
        return p.id == producto_id;
    })

    if (indexProductExists === -1) {
        toastr.error('EL PRODUCTO NO EXISTE EN EL DETALLE DE LA VENTA');
        return;
    }

    lstSale.splice(indexProductExists, 1);

    calculatePrices(lstSale);
    clearTable('tbl_sale_detail');
    paintTableSaleDetail(lstSale);
    paintTableAmounts();

    lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
    paintLstPays(lstPays);

    toastr.info('PRODUCTO ELIMINADO!!!');
    ocultarAnimacion1();
}

const calculatePrices = (lstItems) => {
    const percentageIgv = app.companyIgv;
    let subtotal = 0;
    let total = 0;
    let monto_igv = 0;


    lstItems.forEach((item) => {
        total += (parseFloat(item.sale_price) * parseFloat(item.cant));
    })

    subtotal = total / (1 + (percentageIgv / 100));
    monto_igv = total - subtotal;

    amounts.subtotal = subtotal;
    amounts.monto_igv = monto_igv;
    amounts.total = total;
}

export async function actionAddProduct(e) {
    const product_id = e.target.getAttribute('data-id');
    const product = getRowById(dtProducts, e.target.dataset.id);

    //====== COMPROBANDO SI EXISTE EL PRODUCTO  EN EL SALE DETAIL ========
    const indexProductExists = lstSale.findIndex(p => p.id == product_id);
    let cant = 1;

    if (indexProductExists !== -1) {
        cant = parseFloat(lstSale[indexProductExists].cant) + 1;
    }

    //==== VALIDANDO STOCK =======
    mostrarAnimacion1();
    toastr.clear();
    const resValidateStock = await validateStock({
        product_id,
        cant
    });

    if (resValidateStock.success) {
        toastr.success(resValidateStock.message, 'OPERACIÓN COMPLETADA');

        //======= AGREGANDO AL DETALLE DE LA VENTA =======
        addProductToCar({
            ...product
        }, indexProductExists, cant);
        calculatePrices(lstSale);
        clearTable('tbl_sale_detail');
        paintTableSaleDetail(lstSale);
        paintTableAmounts();

        lstPays[0].amount = parseFloat(amounts.total.toFixed(2));
        paintLstPays(lstPays);

    } else {
        toastr.error(resValidateStock.message, 'ERROR EN EL SERVIDOR');
        e.target.value = resValidateStock.stock;
    }

    ocultarAnimacion1();
}


export function actionRemoveProduct(e) {
    const productId = e.target.dataset.id;
    removeProductFromCar(productId);
    paintCar(lstSale);
}

export function actionPaymentCondition(paymentConditionId) {
    setExpirationDate();
    if (paymentConditionId != 1) {
        hidePayRow();
    } else {
        showPayRow();
    }

}

function setExpirationDate() {

    const selectCondicion = document.getElementById('payment_condition_id');
    const inputFechaRegistro = document.querySelector('#registration_date');
    const inputFechaVencimiento = document.querySelector('#expiration_date');

    const selectedOption = selectCondicion.options[selectCondicion.selectedIndex];
    const days = parseInt(selectedOption.dataset.days || 0, 10);

    const [year, month, day] = inputFechaRegistro.value.split('-');
    const fechaBase = new Date(year, month - 1, day);

    fechaBase.setDate(fechaBase.getDate() + days);

    const yyyy = fechaBase.getFullYear();
    const mm = String(fechaBase.getMonth() + 1).padStart(2, '0');
    const dd = String(fechaBase.getDate()).padStart(2, '0');

    inputFechaVencimiento.value = `${yyyy}-${mm}-${dd}`;
}

function showPayRow() {
    const rowPay = document.querySelector('.row-fade');
    rowPay.classList.remove('hide');
}

function hidePayRow() {
    const rowPay = document.querySelector('.row-fade');
    rowPay.classList.add('hide');
}

export async function actionChangeVehicle(value) {
    document.querySelector('#plate').value = '';
    const vehicleInfo = document.querySelector('#vehicle_info');
    vehicleInfo.classList.add('d-none');
    vehicleInfo.querySelector('.fw-semibold').textContent = '';


    if (!value) return;
    const vehicle = window.vehicleSelect.options[value];
    document.querySelector('#plate').value = vehicle.text;

    vehicleInfo.classList.remove('d-none');
    vehicleInfo.querySelector('.fw-semibold').textContent = vehicle.subtext;

    //========= TRAER CLIENTES ==========
    mostrarAnimacion1();
    try {

        const res = await axios.get(route('tenant.utils.searchCustomer', {
            q: '',
            vehicle_id: value
        }));

        if (res.data.success) {
            toastr.info(res.data.message, 'OPERACIÓN COMPLETADA');
            setCustomerOfVehicle(res.data.data);
        }

    } catch (error) {
        toastr.error(error, 'ERROR AL CARGAR CLIENTE DEL VEHÍCULO');
        return;
    } finally {
        ocultarAnimacion1();
    }
}

function setCustomerOfVehicle(customer) {
    window.clientSelect.clear();
    window.clientSelect.clearOptions();

    customer.forEach(v => {
        window.clientSelect.addOption({
            id: v.id,
            full_name: v.full_name,
            email: v.email
        });
    });

    if (customer.length > 0) {
        window.clientSelect.off('change');
        window.clientSelect.setValue(customer[0].id);
        window.clientSelect.on('change', actionChangeClient);

    }
}

export function actionChangeMethodPay(e) {
    const index = e.target.dataset.index;
    const value = e.target.value;

    lstPays[index].method_pay = value;
}

const addProductToCar = (product, indexProductExists, cant) => {

    if (indexProductExists === -1) {
        product.cant = cant;
        lstSale.push(product);
    } else {
        lstSale[indexProductExists].cant++;
    }

}
