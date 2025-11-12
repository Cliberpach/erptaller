<form action="" id="formUpdatePaymentMethod" method="post">    
    <div class="row">
        @csrf   
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label for="descripcion_edit" style="font-weight: bold;" class="required_field">Nombre</label>
            <div class="input-group ">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-tags"></i>
                </span>
                <input maxlength="160" required id="descripcion_edit" name="descripcion_edit" type="text" class="form-control" placeholder="Método pago" aria-label="Example text with button addon" aria-describedby="button-addon1">
            </div>              
            <span class="descripcion_edit_error msgError_edit"  style="color:red;"></span>
        </div>      
    </div>
</form> 