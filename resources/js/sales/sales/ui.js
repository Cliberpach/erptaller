import { amounts, app } from "./states";

export function paintLstPays(lstPays) {
    const tbody = document.querySelector('#tbl_pay tbody');
    const paymentMethods = app.paymentMethods;
    tbody.innerHTML = '';

    lstPays.forEach((pay, index) => {

        let new_pay = `<tr>
                                    <td>
                                        <select  name="" id="" class="form-control method_pay select2_pay" data-index="${index}" data-placeholder="Seleccionar">
                                            <option></option>
                                `;

        paymentMethods.forEach((p) => {
            new_pay +=
                `<option ${pay.method_pay == p.id ? 'selected' : ''} value="${p.id}">${p.description}</option>`;
        })

        new_pay += `</select>
                                    </td>
                                    <td>
                                        <input data-index="${index}" value="${pay.amount}" type="text" class="form-control amount_pay inputDecimalPositivo">
                                    </td>
                                    <td>
                                        <button class="btn btn-danger btn-sm btn_delete_pay" type="button" data-index="${index}">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>`;

        tbody.insertAdjacentHTML('beforeend', new_pay);
    })

    $('.select2_pay').select2({
        theme: "bootstrap-5",
        width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
        placeholder: $(this).data('placeholder'),
        allowClear: true
    });
}


export const paintTableSaleDetail = (lstItems) => {

    const tbody = document.querySelector('#tbl_sale_detail tbody');
    let rows = '';

    lstItems.forEach((p) => {

        let formattedAmount = (p.sale_price * p.cant).toLocaleString('es-PE', {
            style: 'currency',
            currency: 'PEN',
            minimumFractionDigits: 2
        });

        rows += `<tr>
                            <td>
                                <input style="width:66px;" data-id="${p.id}" value="${p.cant}" type="number" class="form-control inputCantProduct">
                            </td>
                            <td>
                                ${p.name}
                            </td>
                            <td>
                                <div style="display:flex;justify-content:end;">
                                    <input readonly="readonly" value="${formattedAmount}" type="text" class="form-control amount_product_${p.id}">
                                </div>
                            </td>
                            <td>
                                <button type="button" class="btn btn-danger btn-sm delete-product" data-id="${p.id}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>`;
    })

    tbody.innerHTML = rows;
}


export const paintTableAmounts = () => {

    const pOpAmount = document.querySelector('.op-amount');
    const pIgvAmount = document.querySelector('.igv-amount');
    const pTotalAmount = document.querySelector('.total-amount');

    pOpAmount.textContent = amounts.subtotal.toLocaleString('es-PE', {
        style: 'currency',
        currency: 'PEN'
    });
    pIgvAmount.textContent = amounts.monto_igv.toLocaleString('es-PE', {
        style: 'currency',
        currency: 'PEN'
    });
    pTotalAmount.textContent = amounts.total.toLocaleString('es-PE', {
        style: 'currency',
        currency: 'PEN'
    });

}
