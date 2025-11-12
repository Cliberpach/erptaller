<table id="tbl_pay" style="width:100%" class="table table-hover table-dark" > 
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
                <select onchange="changeMethodPay(this)" name="" id="" class="form-control method_pay select2_pay" data-index="0" data-placeholder="Seleccionar">
                    @foreach ($payment_methods as $payment_method)
                        <option 
                        @if ($payment_method->description === 'EFECTIVO') selected @endif 
                        value="{{$payment_method->id}}">{{$payment_method->description}}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" class="form-control amount_pay inputDecimalPositivo" value="0" data-index="0" >            
            </td>
            <td>
                <i class="fas fa-trash-alt btn btn-danger btn_delete_pay" data-index="0"></i>
            </td>
        </tr>
    </tbody>
</table>