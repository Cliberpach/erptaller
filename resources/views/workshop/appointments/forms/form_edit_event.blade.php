<form id="formEditEvent">

    <!-- Título -->
    <div class="mb-3">
        <label for="name_event_edit" class="form-label">
            <i class="fa-solid fa-heading text-primary me-2"></i>Título
        </label>
        <input type="text" class="form-control" id="name_event_edit" name="name_event_edit" placeholder="Título del evento"
            required>
        <p class="name_event_edit_error msgError mb-0"></p>
    </div>

    <!-- Calendario -->
    <div class="mb-3">
        <label for="type_calendar_event_edit" class="form-label">
            <i class="fa-solid fa-calendar text-success me-2"></i>Calendario
        </label>
        <select class="form-select" id="type_calendar_event_edit" name="type_calendar_event_edit" required>
            <option value="PERSONAL">Personal</option>
            <option selected value="TRABAJO">TRABAJO</option>
        </select>
        <p class="type_calendar_event_edit_error msgError mb-0"></p>
    </div>

    <!-- Todo el día -->
    <div class="form-check mb-3">
        <input type="checkbox" class="form-check-input" id="all_day_event_edit" name="all_day_event_edit">
        <label class="form-check-label" for="all_day_event_edit">
            <i class="fa-solid fa-clock text-warning me-2"></i>Todo el día
        </label>
        <p class="all_day_event_edit_error msgError mb-0"></p>
    </div>

    <!-- Fecha y Hora de Inicio -->
    <div class="row mb-3">
        <div class="col-md-7">
            <label for="start_date_event_edit" class="form-label">
                <i class="fa-solid fa-calendar-day text-info me-2"></i>Fecha de inicio
            </label>
            <input type="date" class="form-control" id="start_date_event_edit" name="start_date_event_edit" required>
            <p class="start_date_event_edit_error msgError mb-0"></p>
        </div>

        <div class="col-md-5" id="startTimeContainer">
            <label for="start_time_event_edit" class="form-label">
                <i class="fa-solid fa-clock text-info me-2"></i>Hora
            </label>
            <input type="time" class="form-control" id="start_time_event_edit" name="start_time_event_edit" required>
            <p class="start_time_event_edit_error msgError mb-0"></p>
        </div>
    </div>

    <!-- Fecha y Hora de Fin -->
    <div class="row mb-3">
        <div class="col-md-7">
            <label for="end_date_event_edit" class="form-label">
                <i class="fa-solid fa-calendar-check text-danger me-2"></i>Fecha de fin
            </label>
            <input type="date" class="form-control" id="end_date_event_edit" name="end_date_event_edit" required>
            <p class="end_date_event_edit_error msgError mb-0"></p>
        </div>

        <div class="col-md-5" id="endTimeContainer">
            <label for="end_time_event_edit" class="form-label">
                <i class="fa-solid fa-clock text-danger me-2"></i>Hora
            </label>
            <input type="time" class="form-control" id="end_time_event_edit" name="end_time_event_edit" required>
            <p class="end_time_event_edit_error msgError mb-0"></p>
        </div>
    </div>

    <!-- Ubicación -->
    <div class="mb-3">
        <label for="location_event_edit" class="form-label">
            <i class="fa-solid fa-location-dot text-secondary me-2"></i>Ubicación
        </label>
        <input type="text" class="form-control" id="location_event_edit" name="location_event_edit"
            placeholder="Ubicación del evento">
        <p class="location_event_edit_error msgError mb-0"></p>
    </div>

    <!-- Descripción -->
    <div class="mb-3">
        <label for="description_event_edit" class="form-label">
            <i class="fa-solid fa-align-left text-dark me-2"></i>Descripción
        </label>
        <textarea class="form-control" id="description_event_edit" name="description_event_edit" rows="3"
            placeholder="Descripción del evento"></textarea>
        <p class="description_event_edit_error msgError mb-0"></p>
    </div>

</form>
