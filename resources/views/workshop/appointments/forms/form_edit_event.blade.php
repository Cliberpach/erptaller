<form id="formEditEvent">

    <div class="row">
        <!-- Título -->
        <div class="col-12 mb-3">
            <label for="name_event_edit" class="form-label">
                <i class="fa-solid fa-heading text-primary me-2"></i>Título
            </label>
            <input maxlength="500" type="text" class="form-control" id="name_event_edit" name="name_event_edit"
                placeholder="Título del evento" required>
            <p class="name_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Cliente -->
        <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
            <label class="form-label fw-bold required_field">Cliente:</label>
            <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlNewCustomer();" style="margin-left:4px;"></i>

            <select class="form-control" id="customer_id_event_edit" name="customer_id_event_edit" required>
                <option value="">Seleccione un cliente</option>
            </select>
            <p class="customer_id_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Vehículo -->
        <div class="col-lg-6 col-md-8 col-sm-12 mb-3">
            <label class="form-label fw-bold">Vehículo:</label>
            <i class="fas fa-plus btn btn-warning btn-sm" onclick="openMdlCreateVehicle();"
                style="margin-left:4px;"></i>

            <select class="form-control" id="vehicle_id_event_edit" name="vehicle_id_event_edit">
                <option value="">Seleccionar</option>
            </select>
            <p class="vehicle_id_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Calendario -->
        <div class="col-12 mb-3">
            <label for="type_calendar_event_edit" class="form-label">
                <i class="fa-solid fa-calendar text-success me-2"></i>Calendario
            </label>
            <select class="form-select" id="type_calendar_event_edit" name="type_calendar_event_edit" required>
                <option value="PERSONAL">PERSONAL</option>
                <option selected value="TRABAJO">TRABAJO</option>
            </select>
            <p class="type_calendar_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Todo el día -->
        <div class="col-12 d-none mb-3">
            <input type="checkbox" class="form-check-input" id="all_day_event_edit" name="all_day_event_edit">
            <label class="form-check-label" for="all_day_event_edit">
                <i class="fa-solid fa-clock text-warning me-2"></i>Todo el día
            </label>
            <p class="all_day_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Fecha y Hora de Inicio -->
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <label for="start_date_event_edit" class="form-label">
                <i class="fa-solid fa-calendar-day text-info me-2"></i>Fecha de inicio
            </label>
            <input type="date" class="form-control" id="start_date_event_edit" name="start_date_event_edit" required>
            <p class="start_date_event_edit_error msgError mb-0"></p>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 mb-3" id="startTimeContainer_edit">
            <label for="start_time_event_edit" class="form-label">
                <i class="fa-solid fa-clock text-info me-2"></i>Hora
            </label>
            <input type="time" class="form-control" id="start_time_event_edit" name="start_time_event_edit" required>
            <p class="start_time_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Fecha y Hora de Fin -->
        <div class="col-lg-6 col-md-6 col-sm-12 mb-3">
            <label for="end_date_event_edit" class="form-label">
                <i class="fa-solid fa-calendar-check text-danger me-2"></i>Fecha de fin
            </label>
            <input type="date" class="form-control" id="end_date_event_edit" name="end_date_event_edit" required>
            <p class="end_date_event_edit_error msgError mb-0"></p>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-12 mb-3" id="endTimeContainer_edit">
            <label for="end_time_event_edit" class="form-label">
                <i class="fa-solid fa-clock text-danger me-2"></i>Hora
            </label>
            <input type="time" class="form-control" id="end_time_event_edit" name="end_time_event_edit" required>
            <p class="end_time_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Ubicación -->
        <div class="col-12 mb-3">
            <label for="location_event_edit" class="form-label">
                <i class="fa-solid fa-location-dot text-secondary me-2"></i>Ubicación
            </label>
            <input maxlength="500" type="text" class="form-control" id="location_event_edit"
                name="location_event_edit" placeholder="Ubicación del evento">
            <p class="location_event_edit_error msgError mb-0"></p>
        </div>

        <!-- Descripción -->
        <div class="col-12 mb-3">
            <label for="description_event_edit" class="form-label">
                <i class="fa-solid fa-align-left text-dark me-2"></i>Descripción
            </label>
            <textarea maxlength="500" class="form-control" id="description_event_edit" name="description_event_edit"
                rows="3" placeholder="Descripción del evento"></textarea>
            <p class="description_event_edit_error msgError mb-0"></p>
        </div>

    </div>
</form>
