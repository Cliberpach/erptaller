<form action="" method="post" id="formStoreCustomer">
    @csrf
    <div class="row">

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 mb-2">

            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <label class="required_field" for="type_identity_document" style="font-weight: bold;">TIPO DOCUMENTO</label>
                    <select required name="type_identity_document" required class="form-select select2_form_customer" id="type_identity_document" data-placeholder="Seleccionar" onchange="changeTypeIdentityDocument(this.value)">
                        <option></option>
                        @foreach ($types_identity_documents as $document_identity)
                            <option
                            @if ($document_identity->id == 1)
                                selected
                            @endif
                            value="{{$document_identity->id}}">{{$document_identity->abbreviation}}</option>
                        @endforeach
                    </select>
                    <span class="type_identity_document_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>

                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-2">
                    <label class="required_field" for="nro_document" style="font-weight: bold;">N° DOCUMENTO</label>

                    <div class="input-group">
                        <input maxlength="8" id="nro_document" name="nro_document" required type="text" class="form-control inputEnteroPositivo" placeholder="N° DOCUMENTO" aria-label="Recipient's username" aria-describedby="button-addon2">
                        <button
                        class="btn btn-primary" type="button" id="btn_search_nro_document">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <span class="nro_document_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label class="required_field" for="name" style="font-weight: bold;">NOMBRE</label>
                   <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-user-tag"></i>
                    </span>
                    <input required maxlength="160" id="name" name="name" type="text" class="form-control" placeholder="NOMBRE" aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <span class="name_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <label for="address" style="font-weight: bold;">DIRECCIÓN</label>
                   <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-map-marked-alt"></i>
                    </span>
                    <input maxlength="160" id="address" name="address" type="text" class="form-control" placeholder="DIRECCIÓN" aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <span class="address_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>
            </div>

        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 mb-2">
            <div class="row">
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-2">
                    <label class="required_field" for="department" style="font-weight: bold;">DEPARTAMENTO</label>
                    <select required name="department" required class="form-select select2_form_customer" id="department" data-placeholder="Seleccionar" onchange="changeDepartment(this.value)">
                        <option></option>
                        @foreach ($departments as $department)
                            <option value="{{$department->id}}">{{$department->name}}</option>
                        @endforeach
                    </select>
                    <span class="department_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-2">
                    <label class="required_field" for="province" style="font-weight: bold;">PROVINCIA</label>
                    <select required name="province" required class="form-select select2_form_customer" id="province" data-placeholder="Seleccionar" onchange="changeProvince(this.value)">
                        <option></option>
                    </select>
                    <span class="province_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>
                <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-2">
                    <label class="required_field" for="district" style="font-weight: bold;">DISTRITO</label>
                    <select required name="district" required class="form-select select2_form_customer" id="district" data-placeholder="Seleccionar">
                        <option></option>
                    </select>
                    <span class="district_customer msgErrorCustomer"  style="color:red;"></span>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-2">
                    <label for="phone" style="font-weight: bold;">TELÉFONO</label>
                   <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-phone"></i>
                    </span>
                    <input maxlength="20" id="phone" name="phone" type="text" class="form-control inputNroTelefono" placeholder="TELÉFONO" aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <span class="phone_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>

                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-2">
                    <label for="email" style="font-weight: bold;">CORREO</label>
                   <div class="input-group">
                    <span class="input-group-text" id="basic-addon1">
                        <i class="fas fa-at"></i>
                    </span>
                    <input maxlength="160" id="email" name="email" type="email" class="form-control" placeholder="CORREO" aria-label="Username" aria-describedby="basic-addon1">
                    </div>
                    <span class="email_error_customer msgErrorCustomer"  style="color:red;"></span>
                </div>

            </div>

        </div>
    </div>
</form>
