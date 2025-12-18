<div id="operariosCollapse">

    <h6 class="fw-bold text-secondary mb-3 mt-2">
        Seleccionar Técnicos (Máx: 3)
    </h6>

    <div class="row g-3">

        <div class="col-lg-4 col-md-6 col-sm-12">
            <label class="form-label small fw-bold">Técnico</label>
            <select id="technicians" name="technicians[]" class="select2-tecnicos form-select">
                <option value="">Seleccionar</option>

                @foreach ($technicians as $tec)
                    <option value="{{ $tec->id }}">{{ $tec->name }}</option>
                @endforeach

            </select>
        </div>

    </div>

</div>
