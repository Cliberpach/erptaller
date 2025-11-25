<form action="" id="formRegistrarCargo" method="post">
    <div class="row">
        @csrf
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label for="name" style="font-weight: bold;" class="required_field">Nombre</label>
            <div class="input-group ">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fa-solid fa-tags"></i>
                </span>
                <input required id="name" name="name" type="text" class="form-control" placeholder="Cargo" aria-label="Example text with button addon" aria-describedby="button-addon1">
            </div>
            <span class="name_error msgError"  style="color:red;"></span>
        </div>
    </div>
</form>
