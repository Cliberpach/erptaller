@extends('layouts.template')

@section('title')
    Empresa
@endsection

@section('css')
@endsection

@section('content')

    <div class="row">
        @include('company.forms.form_create')
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
@endsection

@section('css')
@endsection

@section('js')
    <script>
        $(document).on('click', '#btn_consulta_sunat', function() {
            const user_ruc = $('#ruc').val();

            if (user_ruc.length !== 11) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'El RUC debe contener 11 dígitos'
                });
                return;
            }

            Swal.fire({
                title: 'Consultando RUC...',
                text: 'Por favor, espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const url = '/landlord/ruc/' + user_ruc;

            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    Swal.close();
                    if (data.success === false) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'RUC inválido o no existe!'
                        });
                        $('#estado').val(data.message);
                        $('#razon_social').val('');
                        $('#razon_social_abreviada').val('');
                    } else {
                        $('#estado').val(data.data.estado);
                        $('#razon_social').val(data.data.nombre_o_razon_social);
                        $('#razon_social_abreviada').val(data.data.nombre_o_razon_social);
                        $('#direccion_fiscal').val(data.data.direccion_completa);
                        $('#ubigeo').val(data.data.ubigeo_sunat);
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Error al consultar Sunat'
                    });
                }
            });
        });
    </script>

    <script>
        $('#logo').change(function() {
            let reader = new FileReader();
            let file = this.files[0];
            if (file && file.type.startsWith('image/')) {
                reader.onload = (e) => {
                    $('#show-logo').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            } else {
                alert('Seleccione una imagen');
                $('#show-logo').attr('src', null);
                $(this).val('');
            }
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.module-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const childCheckboxes = cardBody.find('.child-checkbox, .child-grandchild-checkbox');
                const grandchildCheckboxes = cardBody.find('.grandchild-checkbox');

                if ($(this).prop('checked')) {
                    childCheckboxes.prop('checked', true);
                    grandchildCheckboxes.prop('checked', true);
                } else {
                    childCheckboxes.prop('checked', false);
                    grandchildCheckboxes.prop('checked', false);
                }
            });

            $('.child-checkbox, .child-grandchild-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const moduleCheckbox = cardBody.find('.module-checkbox');

                if ($(this).prop('checked')) {
                    moduleCheckbox.prop('checked', true);
                } else {
                    let allCheckboxes = cardBody.find('.child-checkbox, .child-grandchild-checkbox');

                    if (allCheckboxes.filter(':checked').length === 0) {
                        moduleCheckbox.prop('checked', false);
                    }
                }
            });

            $('.grandchild-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const childGrandchildCheckboxes = cardBody.find('.child-grandchild-checkbox');
                const childCheckboxes = cardBody.find('.child-checkbox');
                const moduleCheckboxes = cardBody.find('.module-checkbox');

                if ($(this).prop('checked')) {

                    childGrandchildCheckboxes.prop('checked', true);
                    moduleCheckboxes.prop('checked', true);

                } else {
                    let allGrandchildCheckboxes = cardBody.find('.grandchild-checkbox');

                    if (allGrandchildCheckboxes.filter(':checked').length === 0) {
                        childGrandchildCheckboxes.prop('checked', false);

                        if (childCheckboxes.filter(':checked').length === 0)
                            moduleCheckboxes.prop('checked', false);
                    }
                }
            });

            $('.child-grandchild-checkbox').change(function() {
                const cardBody = $(this).closest('.card-body');
                const grandchildCheckboxes = cardBody.find('.grandchild-checkbox');

                if ($(this).prop('checked')) {
                    grandchildCheckboxes.prop('checked', true);
                } else {
                    grandchildCheckboxes.prop('checked', false);
                }
            });
        });
    </script>

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

                    let select = $('#plan_id');
                    select.empty();

                    $.each(data.plans, function(index, plan) {
                        select.append($('<option>', {
                            value: plan.id,
                            text: plan.description
                        }));
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
        document.addEventListener('DOMContentLoaded', () => {
            events();
            loadTomSelect();
        })

        function events() {
            document.querySelector('#form-empresa-store').addEventListener('submit', (e) => {
                e.preventDefault();
                storeEmpresa(e.target);
            })

            document.getElementById('togglePassword').addEventListener('click', function(e) {
                const passwordInput = document.getElementById('password');

                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';

                const icon = e.target.querySelector('i');
                icon.classList.toggle('fa-eye', !isPassword);
                icon.classList.toggle('fa-eye-slash', isPassword);
            });

        }

        function loadTomSelect() {
            const departmentSelect = document.getElementById('department');
            if (departmentSelect && !departmentSelect.tomselect) {
                window.departmentSelect = new TomSelect(departmentSelect, {
                    valueField: 'id',
                    labelField: 'description',
                    searchField: ['description', 'id'],
                    create: false,
                    sortField: { field: 'id', direction: 'asc' },
                    plugins: ['clear_button'],
                    render: {
                        option: (item, escape) => `<div>${escape(item.description)}</div>`,
                        item: (item, escape) => `<div>${escape(item.description)}</div>`
                    }
                });
            }

            const provinceSelect = document.getElementById('province');
            if (provinceSelect && !provinceSelect.tomselect) {
                window.provinceSelect = new TomSelect(provinceSelect, {
                    valueField: 'id',
                    labelField: 'description',
                    searchField: ['description', 'id'],
                    create: false,
                    sortField: { field: 'id', direction: 'asc' },
                    plugins: ['clear_button'],
                    render: {
                        option: (item, escape) => `<div>${escape(item.description)}</div>`,
                        item: (item, escape) => `<div>${escape(item.description)}</div>`
                    }
                });
            }

            const districtSelect = document.getElementById('district');
            if (districtSelect && !districtSelect.tomselect) {
                window.districtSelect = new TomSelect(districtSelect, {
                    valueField: 'id',
                    labelField: 'description',
                    searchField: ['description', 'id'],
                    create: false,
                    sortField: { field: 'id', direction: 'desc' },
                    plugins: ['clear_button'],
                    render: {
                        option: (item, escape) => `<div>${escape(item.description)}</div>`,
                        item: (item, escape) => `<div>${escape(item.description)}</div>`
                    }
                });
            }
        }

        function changeDepartment(department_id) {
            const lstProvinces = @json($provinces);

            if (department_id) {
                department_id = String(department_id).padStart(2, '0');

                const lstProvincesFiltered = lstProvinces.filter((province) => province.department_id == department_id);

                window.provinceSelect.clearOptions();
                lstProvincesFiltered.forEach(province => {
                    window.provinceSelect.addOption({ id: province.id, description: province.name });
                });
                window.provinceSelect.setValue(null);
            }
        }

        function changeProvince(province_id) {
            const lstDistricts = @json($districts);

            if (province_id) {
                province_id = String(province_id).padStart(4, '0');

                const lstDistrictsFiltered = lstDistricts.filter((district) => district.province_id == province_id);

                window.districtSelect.clearOptions();
                lstDistrictsFiltered.forEach(district => {
                    window.districtSelect.addOption({ id: district.id, description: district.name });
                });
                window.districtSelect.setValue(null);
            }
        }

        function storeEmpresa(formCompanyStore) {
            Swal.fire({
                title: '¿Registrar empresa?',
                text: "¡Se crearán un tenant!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-secondary'
                },
                buttonsStyling: false
            }).then(async (result) => {
                if (result.isConfirmed) {
                    try {

                        clearValidationErrors('msgError');

                        Swal.fire({
                            title: 'Registrando empresa...',
                            html: 'Por favor, espera',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        const formData = new FormData(formCompanyStore);
                        const res = await axios.post(route('landlord.mantenimientos.empresas.store'), formData);
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            redirect("landlord.mantenimientos.empresa");
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                            Swal.close();
                        }
                    } catch (error) {
                        Swal.close();
                        if (error.response && error.response.status === 422) {
                            const errors = error.response.data.errors;
                            paintValidationErrors(errors, 'error');
                            toastr.error('ERRORES DE VALIDACIÓN EN EL FORMULARIO');
                            return;
                        } else {
                            toastr.error(error, 'ERROR LA PETICIÓN REGISTRAR EMPRESA');
                        }
                    }
                }
            });
        }
    </script>
@endsection
