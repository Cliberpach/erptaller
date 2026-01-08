<form id="form_alert" method="POST">
    @csrf
    @method('POST')

    <div class="row g-3 mb-4">

        <!-- Nombre de la alerta -->
        <div class="col-md-12">
            <label class="form-label fw-bold">
                <i class="fas fa-bell text-warning me-1"></i> Nombre de la alerta
            </label>
            <input required type="text" class="form-control" id="name" name="name" maxlength="500"
                placeholder="Ej: Próxima atención">
            <p class="name_error msgError mb-0"></p>
        </div>

        <!-- Descripción -->
        <div class="col-md-12">
            <label class="form-label fw-bold">
                <i class="fas fa-align-left text-secondary me-1"></i> Descripción
            </label>
            <textarea class="form-control" maxlength="500" id="description" name="description" rows="3"
                placeholder="Detalle de la alerta (opcional)"></textarea>
            <p class="description_error msgError mb-0"></p>
        </div>

        <!-- Fecha de notificación -->
        <div class="col-md-6">
            <label class="form-label fw-bold">
                <i class="fas fa-calendar-check text-primary me-1"></i> Fecha de notificación
            </label>
            <input required type="date" class="form-control" id="notice_date" name="notice_date"
                min="{{ date('Y-m-d') }}">
            <p class="notice_date_error msgError mb-0"></p>

        </div>

        <!-- Días anticipados -->
        <div class="col-md-6">
            <label class="form-label fw-semibold">
                <i class="fas fa-clock text-warning me-1"></i> Días anticipados
            </label>

            <input type="number" class="form-control" id="advance_days" name="advance_days" min="0"
                max="30" placeholder="Ej: 5" value="0" required>

            <small class="text-muted">
                Valor permitido entre 0 y 30 días
            </small>
            <p class="advance_days_error msgError mb-0"></p>

        </div>



    </div>
</form>
