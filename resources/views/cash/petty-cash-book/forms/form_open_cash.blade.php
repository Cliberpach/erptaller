<form action="" id="form-open-cash">
    <div class="form-group">
        <label for="cash_available_id" class="form-label">Cajas Disponibles</label>
        <select required name="cash_available_id" class="selectCajas form-select" id="cash_available_id" aria-label="Default select example">
            <option value=""></option>
        </select>
        <p hidden class="msgError cash_available_id_error"></p>
    </div>
    <div style="margin-top:11px;" class="form-group pt-2">
        <label for="selectTurnos" class="form-label">Turno</label>
        <select required name="shift" class="form-select" id="shift" aria-label="Default select example">
            <option value="" >Seleccionar</option>
            @foreach ($shiftList as $shift)
                <option value="{{ $shift->id }}">{{ $shift->time }}</option>
            @endforeach
        </select>
        <p hidden class="msgError shift_error"></p>
    </div>
    <div style="margin-top:11px;" class="form-group pt-2">
        <label for="initial_amount" class="form-label">Saldo inicial</label>
        <div class="input-group mb-1">
            <span class="input-group-text">S/.</span>
            <input required value="0" type="text" class="form-control inputDecimalPositivo"
                aria-label="Amount (to the nearest dollar)" id="initial_amount" name="initial_amount">

        </div>
        <p hidden class="msgError errorSaldo"></p>
    </div>
</form>
