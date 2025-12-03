<form action="" id="formUpdateAccount" method="POST">
    <div class="row">
        @csrf

        <div class="col-12 mb-3">
            <label for="holder_edit" class="form-label fw-bold required_field">Titular</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-user"></i>
                </span>
                <input maxlength="160" required id="holder_edit" name="holder_edit" type="text"
                    class="form-control" placeholder="Titular" aria-label="Titular" aria-describedby="basic-addon1">
            </div>
            <span class="holder_edit_error msgError text-danger"></span>
        </div>

        <div class="col-12 mb-3">
            <label for="currency_edit" class="form-label fw-bold required_field">Moneda</label>
            <select name="currency_edit" id="currency_edit" class="form-select select2_account_edit">
                <option value="SOLES">SOLES</option>
                <option value="DOLARES">DÓLARES</option>
            </select>
            <span class="currency_edit_error msgError text-danger"></span>
        </div>

        <div class="col-12 mb-3">
            <label for="account_number_edit" class="form-label fw-bold required_field">N° Cuenta</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon2">
                    <i class="fas fa-university"></i>
                </span>
                <input maxlength="160" required id="account_number_edit" name="account_number_edit" type="text"
                    class="form-control" placeholder="N° Cuenta" aria-label="N° Cuenta" aria-describedby="basic-addon2">
            </div>
            <span class="account_number_edit_error msgError text-danger"></span>
        </div>

        <div class="col-12 mb-3">
            <label for="cci_edit" class="form-label fw-bold required_field">CCI</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon3">
                    <i class="fas fa-exchange-alt"></i>
                </span>
                <input maxlength="100" id="cci_edit" name="cci_edit" type="text" class="form-control"
                    placeholder="CCI" aria-label="CCI" aria-describedby="basic-addon3">
            </div>
            <span class="cci_edit_error msgError text-danger"></span>
        </div>

        <div class="col-12 mb-3">
            <label for="phone_edit" class="form-label fw-bold required_field">Celular</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon4">
                    <i class="fas fa-mobile-alt"></i>
                </span>
                <input maxlength="100" id="phone_edit" name="phone_edit" type="text" class="form-control"
                    placeholder="Celular" aria-label="Celular" aria-describedby="basic-addon4">
            </div>
            <span class="phone_edit_error msgError text-danger"></span>
        </div>

        <div class="col-12 mb-3">
            <label for="bank_id_edit" class="form-label fw-bold required_field">Banco</label>
            <select name="bank_id_edit" id="bank_id_edit" class="form-select select2_account_edit">
                @foreach ($banks as $bank)
                    <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                @endforeach
            </select>
            <span class="bank_id_edit_error msgError text-danger"></span>
        </div>

    </div>
</form>
