<table id="tbl_pay" style="width:100%" class="table-hover table-dark table">
    <thead>
        <tr>
            <th style="width: 50%;">MÉT. PAGO</th>
            <th style="width: 44%;">MONTO</th>
            <th></th>
        </tr>
    </thead>
    <tbody class="body-table">
        <tr>
            <td>
                <select name="" id=""
                    class="form-control method_pay select2_pay" data-index="0" data-placeholder="Seleccionar">
                    @foreach ($payment_methods as $payment_method)
                        <option @if ($payment_method->description === 'EFECTIVO') selected @endif value="{{ $payment_method->id }}">
                            {{ $payment_method->description }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control amount_pay inputDecimalPositivo" value="0"
                    data-index="0">
            </td>
            <td>
                <button class="btn btn-danger btn-sm btn_delete_pay" type="button" data-index="0">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>
