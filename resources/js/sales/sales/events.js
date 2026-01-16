import { actionChangeClient } from "../../utils/action";
import { actionAddPay, actionAddProduct, actionAmountPay, actionCantProduct, actionChangeMethodPay, actionChangeVehicle, actionDeletePay, actionDeleteProduct, actionPaymentCondition, actionRemoveProduct, actionStore } from "./action";
import { app } from "./states";

export function events() {
    app.init();

    eventsDataTable();
    eventsClick();
    eventsChange();
    eventsInput();

}

function eventsDataTable() {
    $('#tbl_products').DataTable().on('search.dt', function (e, settings) {

        const searchValue = settings.oPreviousSearch.search;
        console.log('El usuario buscó:', settings.oPreviousSearch.search);

        $('#tbl_products').DataTable().one('draw', function () {
            const filteredRows = $('#tbl_products').DataTable().rows({
                search: 'applied'
            }).data();

            if (filteredRows.length === 1) {

                console.log('Filas filtradas después de la búsqueda:', filteredRows.toArray());
                const product_id = filteredRows[0].id;
                const element = document.querySelector(`.btnAdd[data-id="${product_id}"]`);
                element.click();

                $('#dt-search-0').val('');

            }

        });
    });

}

function eventsChange() {
    document.addEventListener('change', (e) => {

        if (e.target.classList.contains('amount_pay')) {
            actionAmountPay(e);
        }

        if (e.target.classList.contains('method_pay')) {
            actionChangeMethodPay(e);
        }

    })


    document.querySelector('#payment_condition_id').addEventListener('change', function (e) {
        actionPaymentCondition(e.target.value);
    });

    window.clientSelect.on('change', function (value) {
        actionChangeClient(value);
    });

    window.vehicleSelect.on('change', function (value) {
        actionChangeVehicle(value);
    });
}


function eventsInput() {
    document.addEventListener('input', async (e) => {
        if (e.target.classList.contains('inputCantProduct')) {
            actionCantProduct(e);
        }
    })

    document.querySelector('#form-store').addEventListener('submit', (e) => {
        actionStore(e);
    })
}

function eventsClick() {
    document.addEventListener('click', async (e) => {

        if (e.target.closest('.btn_delete_pay')) {
            actionDeletePay(e);
        }

        if (e.target.closest('.delete-product')) {
            actionDeleteProduct(e);
        }

        if (e.target.classList.contains('btnAdd')) {
            actionAddProduct(e);
        }

        if (e.target.classList.contains('remove-product')) {
            actionRemoveProduct(e);
        }
    })

    document.querySelector('.btnAddPay').addEventListener('click', (e) => {
        actionAddPay();
    })
}
