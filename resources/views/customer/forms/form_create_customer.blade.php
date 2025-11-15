<form id="createCustomerForm" method="POST" novalidate>
    @csrf

    <div class="row g-3">
        <!-- Tipo Documento -->
        <div class="col-md-6 col-lg-4">
            <label for="type_document_id" class="form-label fw-bold required_field">Tipo de Documento (*)</label>
            <select id="type_document_id" name="type_document_id" class="form-select" required>
                <option value="">Seleccione un tipo de documento</option>
                <option value="1">DNI</option>
                <option value="2">RUC</option>
            </select>
            <p class="type_document_id_error msgError mb-0"></p>
        </div>

        <!-- N° Documento -->
        <div class="col-md-6 col-lg-4">
            <label for="document_number" class="form-label fw-bold required_field">N° Documento (*)</label>
            <div class="input-group">
                <input
                    type="text"
                    id="document_number"
                    name="document_number"
                    class="form-control"
                    value="{{ old('document_number') }}"
                    placeholder="Ingrese número de documento"
                    required
                >
                <button class="btn btn-outline-primary" type="button" id="btn_consulta_sunat">
                    <i class="bx bx-search"></i>
                </button>
            </div>
            <p class="document_number_error msgError mb-0"></p>
        </div>

        <!-- Nombre -->
        <div class="col-md-12 col-lg-4">
            <label for="name" class="form-label fw-bold required_field">Nombre (*)</label>
            <input
                type="text"
                id="name"
                name="name"
                class="form-control"
                placeholder="Ingrese nombre"
                value="{{ old('name') }}"
                required
            >
            <p class="name_error msgError mb-0"></p>
        </div>

        <!-- Celular -->
        <div class="col-md-6 col-lg-4">
            <label for="phone" class="form-label fw-bold required_field">Celular (*)</label>
            <input
                type="text"
                id="phone"
                name="phone"
                class="form-control"
                placeholder="Ingrese número de celular"
                value="{{ old('phone') }}"
                required
            >
            <p class="phone_error msgError mb-0"></p>
        </div>

        <!-- Correo -->
        <div class="col-md-6 col-lg-4">
            <label for="email" class="form-label fw-bold">Correo</label>
            <input
                type="email"
                id="email"
                name="email"
                class="form-control"
                maxlength="160"
                placeholder="correo@ejemplo.com"
                value="{{ old('email') }}"
            >
            <p class="email_error msgError mb-0"></p>
        </div>

        <!-- Ubigeo -->
        <div class="col-md-6 col-lg-4">
            <label for="ubigeo_id" class="form-label fw-bold required_field">Ubigeo (*)</label>
            <select
                id="ubigeo_id"
                name="ubigeo_id"
                class="form-select"
                required
            >
                <option value="">Seleccione un ubigeo</option>
                <!-- Puedes cargarlo dinámicamente -->
            </select>
            <p class="ubigeo_id_error msgError mb-0"></p>
        </div>

        <!-- Dirección -->
        <div class="col-md-12">
            <label for="address" class="form-label fw-bold">Dirección</label>
            <input
                type="text"
                id="address"
                name="address"
                class="form-control"
                maxlength="160"
                placeholder="Ingrese dirección (máx. 160 caracteres)"
                value="{{ old('address') }}"
            >
            <p class="address_error msgError mb-0"></p>
        </div>

    </div>
</form>
