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
        </div>

        <!-- Descripción -->
        <div class="col-md-12">
            <label class="form-label fw-bold">
                <i class="fas fa-align-left text-secondary me-1"></i> Descripción
            </label>
            <textarea class="form-control" maxlength="500" id="description" name="description" rows="3"
                placeholder="Detalle de la alerta (opcional)"></textarea>
        </div>

        <!-- Fecha de notificación -->
        <div class="col-md-6">
            <label class="form-label fw-bold">
                <i class="fas fa-calendar-check text-primary me-1"></i> Fecha de notificación
            </label>
            <input required type="date" class="form-control" id="notice_date" name="notice_date"
                min="{{ date('Y-m-d') }}">
        </div>

        <!-- Fecha anticipada -->
        <div class="col-md-6">
            <label class="form-label fw-bold">
                <i class="fas fa-calendar-alt text-danger me-1"></i> Fecha anticipada
            </label>
            <input required type="date" class="form-control" id="advance_date" name="advance_date"
                min="{{ date('Y-m-d') }}">
        </div>

    </div>
</form>
