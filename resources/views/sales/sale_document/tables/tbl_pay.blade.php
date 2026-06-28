<table id="tbl_pay" style="width:100%" class="table-hover table-dark table">
    <thead>
        <tr>
            <th style="width: 28%;">MÉT. PAGO</th>
            <th style="width: 30%;">CUENTA</th>
            <th style="width: 18%;">N° OPERACIÓN</th>
            <th style="width: 18%;">MONTO</th>
            <th></th>
        </tr>
    </thead>
    <tbody class="body-table">
        <tr>
            <td>
                <select onchange="changeMethodPay(this)" name="" id=""
                    class="form-control method_pay select2_pay" data-index="0" data-placeholder="Seleccionar">
                    @foreach ($payment_methods as $payment_method)
                        <option @if ($payment_method->description === 'EFECTIVO') selected @endif value="{{ $payment_method->id }}">
                            {{ $payment_method->description }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                {{-- Combo cuenta dependiente del método (oculto para efectivo) --}}
                <select class="form-control account_pay" data-index="0" style="display:none;">
                    <option value="">Seleccionar cuenta</option>
                </select>
            </td>
            <td>
                <input type="text" class="form-control operation_pay" data-index="0"
                    placeholder="N° op." style="display:none;">
            </td>
            <td>
                <input type="text" class="form-control amount_pay inputDecimalPositivo" value="0"
                    data-index="0">
            </td>
            <td>
                <button class="btn btn-danger btn-sm btn_delete_pay" data-index="0">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        </tr>
    </tbody>
</table>
