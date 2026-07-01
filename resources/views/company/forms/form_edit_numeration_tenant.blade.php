<form action="" method="post" id="formBillingNumeration">

  <div class="row">
    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
        <label class="required_field" for="billing_type_document" style="font-weight: bold;">TIPO COMPROBANTE</label>
        <select required name="billing_type_document" required class="form-select select2_form_numeration" id="billing_type_document" data-placeholder="Seleccionar" onchange="setBillingType(this.value)">
            <option></option>
            @foreach ($billing_documents as $billing_document)
                <option value="{{$billing_document->id}}">{{$billing_document->description}}</option>
            @endforeach
        </select>
        <span class="billing_document_error_customer msgError"  style="color:red;"></span>
    </div>
    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
        <label class="required_field" for="serie" style="font-weight: bold;">SERIE</label>
        <input readonly type="text" name="serie" id="serie" class="form-control">
        <span class="serie_error msgError"  style="color:red;"></span>
    </div>
    <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
        <label class="required_field" for="start_number" style="font-weight: bold;">NÚMERO INICIO</label>
        <input required value="1" maxlength="8" type="text" name="start_number" id="start_number" class="form-control inputEnteroPositivo">
        <span class="start_number_error msgError"  style="color:red;"></span>
    </div>
</div>
</form>