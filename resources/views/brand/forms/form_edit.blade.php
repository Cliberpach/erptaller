    <form id="formUpdateBrand" action="" method="POST">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="form-group">
                <label for="name_edit" class="form-label">Nombre <span>*</span></label>
                <input id="name_edit" name="name_edit" type="text" class="form-control inputName"
                    placeholder="Nombre de la categoría" oninput="this.value = this.value.toUpperCase()">
                <p class="msgError_edit name_edit_error" style="font-weight: bold;color:rgb(208, 11, 11);"></p>
            </div>
        </div>
    </form>
