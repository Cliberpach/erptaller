<form action="" id="formRegistrarSede" method="post">
    <div class="row">
        @csrf
        <div class="col-lg-8 col-md-8 col-sm-12 mb-3">
            <label for="nombre" style="font-weight: bold;" class="required_field">Nombre</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-building"></i></span>
                <input required id="nombre" name="nombre" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Nombre de la sede">
            </div>
            <span class="nombre_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-4 col-md-4 col-sm-12 mb-3">
            <label for="codigo" style="font-weight: bold;" class="required_field">Código</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-hashtag"></i></span>
                <input required id="codigo" name="codigo" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Ej. S002">
            </div>
            <span class="codigo_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12 mb-3">
            <label for="direccion" style="font-weight: bold;">Dirección</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-location-dot"></i></span>
                <input id="direccion" name="direccion" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Dirección de la sede">
            </div>
            <span class="direccion_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <label for="telefono" style="font-weight: bold;">Teléfono</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-phone"></i></span>
                <input id="telefono" name="telefono" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Teléfono">
            </div>
            <span class="telefono_error msgError" style="color:red;"></span>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <label for="ubigeo" style="font-weight: bold;">Ubigeo</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fa-solid fa-map"></i></span>
                <input id="ubigeo" name="ubigeo" type="text" class="form-control"
                    style="background-color:#FFF9C4;" placeholder="Ubigeo (6 dígitos)">
            </div>
            <span class="ubigeo_error msgError" style="color:red;"></span>
        </div>
    </div>
</form>
