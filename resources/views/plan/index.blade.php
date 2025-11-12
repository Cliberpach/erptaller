@extends('layouts.template')

@section('title')
    Empresa
@endsection

@section('css')
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">MANTENIMIENTO DE PLANES</h5>
            <span class="float-end">
                <button type="button" class="btn btn-outline-primary me-1" data-bs-toggle="modal"
                    data-bs-target="#modal_create_plan">
                    Nuevo Plan
                </button>
            </span>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table text-center" id="table_fields">
                <thead>
                    <tr>
                        <th>DESCRIPCIÓN</th>
                        <th>CAMPOS</th>
                        <th>PRECIO</th>
                        <th>ACCIONES</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @foreach ($plans as $plan)
                        <tr>
                            <td>{{ $plan->description }}</td>
                            <td>{{ $plan->number_fields }}</td>
                            <td>S/ {{ $plan->price }}</td>
                            <td>
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary btn-sm"
                                        onclick="editPlan({{ $plan->id }})">
                                        <i class="bx bx-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-danger btn-sm"
                                        onclick="deletePlan({{ $plan->id }})">
                                        <i class="bx bx-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="modal_create_plan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="frm_plan">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Nuevo Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col mb-3">
                                <label for="description" class="form-label">Descripción</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-notepad'></i></span>
                                    <input type="text" class="form-control" placeholder="Descripción del plan"
                                        id="description" name="description">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="description_error"></p>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="number_fields" class="form-label">Número de Campos</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-dots-horizontal-rounded'></i></span>
                                    <input type="text" class="form-control"
                                        placeholder="Número de campos permitodos en el plan" id="number_fields"
                                        name="number_fields">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="number_fields_error"></p>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="price" class="form-label">Precio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-dollar'></i></span>
                                    <input type="text" class="form-control" placeholder="Precio del plan" id="price"
                                        name="price">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="price_error"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_guardar">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="modal_update_plan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="frm_plan_update">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Actualizar Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <input type="hidden" id="e_id">
                            <div class="col mb-3">
                                <label for="e_description" class="form-label">Descripción</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-notepad'></i></span>
                                    <input type="text" class="form-control" id="e_description" name="description">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="e_description_error">
                        </p>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="e_number_fields" class="form-label">Número de Campos</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-dots-horizontal-rounded'></i></span>
                                    <input type="text" class="form-control" id="e_number_fields"
                                        name="number_fields">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="e_number_fields_error"></p>
                        <div class="row">
                            <div class="col mb-3">
                                <label for="e_price" class="form-label">Precio</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class='bx bx-dollar'></i></span>
                                    <input type="text" class="form-control" id="e_price" name="price">
                                </div>
                            </div>
                        </div>
                        <p style="color: red; margin-top: -10px;" id="e_price_error"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-primary" id="btn_actualizar">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div class="modal fade" id="modal_delete_plan" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="frm_plan_delete">
                    @csrf
                    @method('DELETE')
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel1">Eliminar Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" id="d_id">
                        <p id="d_message"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cerrar
                        </button>
                        <button type="submit" class="btn btn-danger" id="btn_eliminar">Eliminar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        $("#frm_plan").on("submit", function(e) {
            e.preventDefault();
            $.ajax({
                url: '{{ route('landlord.mantenimientos.planes.store') }}',
                method: 'POST',
                dataType: 'json',
                data: new FormData($("#frm_plan")[0]),
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#btn_guardar').attr("disabled", true);
                    $('#btn_guardar').html(
                        '<div class="spinner-border spinner-border-sm text-white" role="status"></div> Guardando...'
                    );
                },
                success: function(data) {
                    $('#modal_create_plan').modal('hide');
                    $('#frm_plan')[0].reset();
                    toastr.success(data.message, 'Crear Registro', {
                        timeOut: 3000
                    });

                    $('#table_fields tbody').empty();

                    $.each(data.plans, function(index, plan) {
                        $('#table_fields tbody').append(
                            '<tr>' +
                            '<td>' + plan.description + '</td>' +
                            '<td>' + plan.number_fields + '</td>' +
                            '<td>S/ ' + plan.price + '</td>' +
                            '<td>' +
                            '<div class="btn-group">' +
                            '<button type="button" class="btn btn-primary btn-sm" onclick="editPlan(' +
                            plan.id + ')"><i class="bx bx-edit"></i></button>' +
                            '<button type="button" class="btn btn-danger btn-sm" onclick="deletePlan(' +
                            plan.id + ')"><i class="bx bx-trash"></i></button>' +
                            '</div>' +
                            '</td>' +
                            '</tr>'
                        );
                    });

                    $('#description_error').text('');
                    $('#number_fields_error').text('');
                    $('#price_error').text('');
                },
                error: function(data) {
                    let errores = data.responseJSON.errors;

                    errores.hasOwnProperty('description') ? $('#description_error').text(
                        `* ${errores.description[0]}`) : $('#description_error').text('');

                    errores.hasOwnProperty('number_fields') ? $('#number_fields_error').text(
                        `* ${errores.number_fields[0]}`) : $('#number_fields_error').text('');

                    errores.hasOwnProperty('price') ? $('#price_error').text(`* ${errores.price[0]}`) :
                        $('#price_error').text('');

                    $('#btn_guardar').text('Registrar');
                    $('#btn_guardar').attr("disabled", false);
                },
                complete: function() {
                    $('#btn_guardar').text('Guardar');
                    $('#btn_guardar').attr("disabled", false);
                },
            });
        });
    </script>

    <script>
        function editPlan(plan_id) {
            $.get('/mantenimiento/plan/edit/' + plan_id, function(plan) {
                $('#e_id').val(plan.data.id);
                $('#e_description').val(plan.data.description);
                $('#e_number_fields').val(plan.data.number_fields);
                $('#e_price').val(plan.data.price);
                $("input[name=_token]").val();
                $('#modal_update_plan').modal('toggle');
            })
        }
    </script>

    <script>
        $('#frm_plan_update').submit(function(e) {
            e.preventDefault();
            let e_id = $('#e_id').val();

            $.ajax({
                url: "/mantenimiento/plan/update/" + e_id,
                method: 'POST',
                dataType: 'json',
                data: new FormData($("#frm_plan_update")[0]),
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#btn_actualizar').attr("disabled", true);
                    $('#btn_actualizar').html(
                        '<div class="spinner-border spinner-border-sm text-white" role="status"></div> Actualizando...'
                    );
                },
                success: function(data) {
                    $('#modal_update_plan').modal('hide');
                    toastr.success(data.message, 'Actualizar Registro', {
                        timeOut: 3000
                    });

                    $('#table_fields tbody').empty();

                    $.each(data.plans, function(index, plan) {
                        $('#table_fields tbody').append(
                            '<tr>' +
                            '<td>' + plan.description + '</td>' +
                            '<td>' + plan.number_fields + '</td>' +
                            '<td>S/ ' + plan.price + '</td>' +
                            '<td>' +
                            '<div class="btn-group">' +
                            '<button type="button" class="btn btn-primary btn-sm" onclick="editPlan(' +
                            plan.id + ')"><i class="bx bx-edit"></i></button>' +
                            '<button type="button" class="btn btn-danger btn-sm" onclick="deletePlan(' +
                            plan.id + ')"><i class="bx bx-trash"></i></button>' +
                            '</div>' +
                            '</td>' +
                            '</tr>'
                        );
                    });

                    $('#description_error').text('');
                    $('#number_fields_error').text('');
                    $('#price_error').text('');
                },
                complete: function() {
                    $('#btn_actualizar').text('Actualizar');
                    $('#btn_actualizar').attr("disabled", false);
                },
            })

        });
    </script>

    <script>
        function deletePlan(plan_id) {
            $.get('/mantenimiento/plan/delete/' + plan_id, function(plan) {
                $('#d_id').val(plan.data.id);
                $('#d_message').html(
                    `¿Estas seguro de eliminar el plan: <b style="color: red">${plan.data.description}</b> de nuestra data?`
                    );
                $("input[name=_token]").val();
                $('#modal_delete_plan').modal('toggle');
            })
        }
    </script>

    <script>
        $('#frm_plan_delete').submit(function(e) {
            e.preventDefault();
            let d_id = $('#d_id').val();

            $.ajax({
                url: "/mantenimiento/plan/destroy/" + d_id,
                method: 'POST',
                dataType: 'json',
                data: new FormData($("#frm_plan_delete")[0]),
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#btn_eliminar').attr("disabled", true);
                    $('#btn_eliminar').html(
                        '<div class="spinner-border spinner-border-sm text-white" role="status"></div> Eliminando...'
                    );
                },
                success: function(data) {
                    $('#modal_delete_plan').modal('hide');
                    toastr.error(data.message, 'Eliminar Registro', {
                        timeOut: 3000
                    });

                    $('#table_fields tbody').empty();

                    $.each(data.plans, function(index, plan) {
                        $('#table_fields tbody').append(
                            '<tr>' +
                            '<td>' + plan.description + '</td>' +
                            '<td>' + plan.number_fields + '</td>' +
                            '<td>S/ ' + plan.price + '</td>' +
                            '<td>' +
                            '<div class="btn-group">' +
                            '<button type="button" class="btn btn-primary btn-sm" onclick="editPlan(' +
                            plan.id + ')"><i class="bx bx-edit"></i></button>' +
                            '<button type="button" class="btn btn-danger btn-sm" onclick="deletePlan(' +
                            plan.id + ')"><i class="bx bx-trash"></i></button>' +
                            '</div>' +
                            '</td>' +
                            '</tr>'
                        );
                    });
                },
                complete: function() {
                    $('#btn_eliminar').text('Eliminar');
                    $('#btn_eliminar').attr("disabled", false);
                },
            })

        });
    </script>
@endsection
