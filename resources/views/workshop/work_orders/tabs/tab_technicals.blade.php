<div id="operariosCollapse">

    <h6 class="fw-bold text-secondary mb-3 mt-2">
        Seleccionar Técnicos (Máx: 3)
    </h6>

    <div class="row g-3">

        <div class="col-12">
            <label class="form-label small fw-bold">Técnicos</label>

            <div class="table-responsive">
                <table class="table-bordered table-hover table-sm table align-middle" id="dt-technicians">
                    <thead class="table-light text-center">
                        <tr>
                            <th>ID</th>
                            <th>Seleccionar</th>
                            <th>Nombre</th>
                            <th>Tipo Doc.</th>
                            <th>N° Documento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($technicians as $tec)
                            <tr>
                                <td class="text-center">{{ $tec->id }}</td>

                                <td class="text-center">
                                    <input type="checkbox" value="{{ $tec->id }}"
                                        class="form-check-input chk-technical" data-id="{{ $tec->id }}">
                                </td>

                                <td>{{ $tec->name }}</td>

                                <td class="text-center">
                                    {{ $tec->document_type_abbreviation }}
                                </td>

                                <td class="text-center">
                                    {{ $tec->document_number }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


    </div>
</div>
