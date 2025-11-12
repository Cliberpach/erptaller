<form action="" id="formGenerateDocument">
    <div class="row">
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
            <label class="required_field" for="document_invoice" style="font-weight: bold;">TIPO COMPROBANTE</label>
            <select required name="document_invoice" required class="form-select select2_form" id="document_invoice" data-placeholder="Seleccionar" >
                <option></option>
                @foreach ($document_actives as $document)
                    <option 
                    @if ($document->document_type_id === 3)
                        selected
                    @endif value="{{$document->document_type_id}}">{{$document->description}}</option>
                @endforeach
            </select>
            <span class="document_invoice_error msgError"  style="color:red;"></span>
        </div>
        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
            <label class="required_field" for="document_number" style="font-weight: bold;">N° DOCUMENTO IDENTIDAD</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-address-card"></i>
                </span>
                <input required value="{{$customer->document_number}}"  name="document_number"
                type="text" id="document_number" class="form-control inputEnteroPositivo" placeholder="N° DOCUMENTO" 
                aria-label="Username" aria-describedby="basic-addon1">                    
            </div>
            <span class="document_number_error msgError"  style="color:red;"></span>
        </div>

        <div class="col-12" style="display: flex;justify-content:end;">
            <button class="btn btn-danger" type="button" style="margin-right: 4px;" onclick="goBack();">
                <i class="fa-solid fa-right-from-bracket"></i> REGRESAR
            </button>
            <button class="btn btn-primary" form="formGenerateDocument"><i class="fa-solid fa-floppy-disk"></i> GRABAR</button>
        </div>
    </div>
</form>