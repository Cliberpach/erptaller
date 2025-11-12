@extends('layouts.template')

@section('title')
    Consultas Reservas
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    @include('consultas.reservas.modals.generar_documento')
    <div class="container mt-4">
        <h4 class="mb-4">Consultas Reservas</h4>

        <!-- Formulario de Búsqueda -->
        <div class="card">
            <div class="card-body">
                <form id="searchForm">
                    <div class="row g-3 align-items-end">
                        <div class="col-lg-2 col-md-4">
                            <label for="search_type" class="form-label fw-bold">Buscar por</label>
                            <select class="form-select" id="search_type">
                                <option value="dni">DNI</option>
                                <option value="ruc">RUC</option>
                                <option value="nombre">NOMBRE</option>
                                <option value="razon_social">RAZÓN SOCIAL</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="search_input" class="form-label fw-bold" id="search_input_label">Filtro</label>
                            <input type="text" class="form-control" id="search_input" placeholder="Ingrese valor">
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="start_date" class="form-label fw-bold">Desde</label>
                            <input type="date" class="form-control" id="start_date">
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="end_date" class="form-label fw-bold">Hasta</label>
                            <input type="date" class="form-control" id="end_date">
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label for="search_estado" class="form-label fw-bold">ESTADO PAGO</label>
                            <select class="form-select" id="search_estado">
                                <option value="SIN PAGO" selected>SIN PAGO</option>
                                <option value="PARCIAL">PARCIAL</option>
                                <option value="TOTAL">TOTAL</option>
                            </select>
                        </div>

                        <div class="col-lg-2 col-md-4">
                            <label class="form-label fw-bold d-block">&nbsp;</label>
                            <div class="btn-group w-100" role="group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bx bx-search-alt-2 me-1"></i>
                                </button>
                                <button type="button" class="btn btn-danger" id="btnExportPdf" title="Exportar a PDF">
                                    <i class="bx bxs-file-pdf"></i>
                                </button>
                                <button type="button" class="btn btn-success" id="btnExportExcel" title="Exportar a Excel">
                                    <i class="bx bxs-file-export"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>



        <!-- Tabla de Resultados -->
        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title">Resultados</h5>
                <div class="table-responsive">
                    @include('consultas.reservas.tables.tbl_list_reservas')
                </div>

                <div class="d-flex justify-content-between mt-3" id="accionesPago">
                    {{-- <button type="button" class="btn btn-outline-secondary" id="btnSeleccionarTodos">
                        <i class="bx bx-check-square me-1"></i> Seleccionar Todos
                    </button> --}}

                    <button type="button" class="btn btn-success" id="btnPagarSeleccionados" onclick="openMdlDocument();">
                        <i class="bx bx-dollar-circle me-1"></i> Pagar Seleccionados
                    </button>
                </div>


            </div>
        </div>
    </div>
@endsection


@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            initDefaultDates();
            initSearchTypeEvents();
            initInputValidation();
            document.getElementById('search_estado').addEventListener('change', handlePagoActionsVisibility);
            initExportPDF();
            initExportExcel();

            initDataTable();

            events();
        });

        function events() {
            eventsMdlDocument();

            document.querySelector('#searchForm').addEventListener('submit', function(e) {
                e.preventDefault();

                const form = document.getElementById('searchForm');
                const type = document.getElementById('search_type');
                const input = document.getElementById('search_input');
                const value = input.value.trim();

                if (type.value == 3 && value.length !== 8) {
                    showSwalError('DNI inválido', 'Debe contener exactamente 8 dígitos.');
                    return;
                }

                if (type.value === 1 && value.length !== 11) {
                    showSwalError('RUC inválido', 'Debe contener exactamente 11 dígitos.');
                    return;
                }

                if ((type.value === 'nombre' || type.value === 'razon_social') && value.length < 3) {
                    showSwalError('Campo inválido', 'Ingrese al menos 3 caracteres para buscar.');
                    return;
                }


                if (window.dtReservations) {
                    window.dtReservations.ajax.reload();
                }

                resetParameters();
                toastr.clear();
                toastr.success('FORMULARIO LIMPIO');
            });
        }

        function initExportExcel() {
            const excelButton = document.getElementById('btnExportExcel');
            if (!excelButton) return;

            excelButton.addEventListener('click', handleExcelExport);
        }

        function handleExcelExport() {
            const searchType = document.getElementById('search_type').value;
            const searchInput = document.getElementById('search_input').value.trim();
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (searchType === 'dni' && searchInput.length !== 8) {
                showSwalError('DNI inválido', 'Debe contener exactamente 8 dígitos.');
                return;
            }

            if (searchType === 'ruc' && searchInput.length !== 11) {
                showSwalError('RUC inválido', 'Debe contener exactamente 11 dígitos.');
                return;
            }

            if ((searchType === 'nombre' || searchType === 'razon_social') && searchInput.length < 3) {
                showSwalError('Campo inválido', 'Ingrese al menos 3 caracteres para buscar.');
                return;
            }

            const url = new URL("{{ route('tenant.consultas.creditos.excel') }}", window.location.origin);
            url.searchParams.set('search_type', searchType);
            url.searchParams.set('search_input', searchInput);
            url.searchParams.set('start_date', startDate);
            url.searchParams.set('end_date', endDate);
            url.searchParams.set('search_estado', document.getElementById('search_estado').value);


            window.open(url.toString(), '_blank');
        }



        function handlePagoActionsVisibility() {
            const estado = document.getElementById('search_estado').value;
            const acciones = document.getElementById('accionesPago');

            if (estado === 'PAGADO') {
                acciones.classList.add('d-none');
            } else {
                acciones.classList.remove('d-none');
            }
        }



        function initDefaultDates() {
            const startDate = document.getElementById('start_date');
            const endDate = document.getElementById('end_date');

            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            startDate.value = formatDate(firstDay);
            endDate.value = formatDate(lastDay);
        }

        function formatDate(date) {
            return date.toISOString().split('T')[0];
        }

        function initSearchTypeEvents() {
            const searchType = document.getElementById('search_type');
            const searchInput = document.getElementById('search_input');

            searchType.addEventListener('change', () => {
                searchInput.value = '';
                updateInputConfig(searchType.value);
            });

            updateInputConfig(searchType.value);
        }

        function updateInputConfig(type) {
            const input = document.getElementById('search_input');
            const label = document.getElementById('search_input_label');

            const config = {
                dni: {
                    max: 8,
                    placeholder: 'Ingrese DNI (8 dígitos)',
                    label: 'DNI'
                },
                ruc: {
                    max: 11,
                    placeholder: 'Ingrese RUC (11 dígitos)',
                    label: 'RUC'
                },
                nombre: {
                    max: 100,
                    placeholder: 'Ingrese Nombre',
                    label: 'Nombre'
                },
                razon_social: {
                    max: 100,
                    placeholder: 'Ingrese Razón Social',
                    label: 'Razón Social'
                }
            };

            const selected = config[type] || {
                max: 100,
                placeholder: 'Ingrese valor',
                label: 'Filtro'
            };

            input.setAttribute('maxlength', selected.max);
            input.setAttribute('placeholder', selected.placeholder);
            label.textContent = selected.label;
        }



        function initInputValidation() {
            const input = document.getElementById('search_input');
            const typeSelect = document.getElementById('search_type');

            input.addEventListener('input', () => {
                const type = typeSelect.value;

                if (type === 'dni') {
                    input.value = input.value.replace(/\D/g, '').slice(0, 8);
                } else if (type === 'ruc') {
                    input.value = input.value.replace(/\D/g, '').slice(0, 11);
                } else if (type === 'nombre' || type === 'razon_social') {
                    input.value = input.value.replace(/[^a-zA-ZÁÉÍÓÚÑáéíóúñ\s]/g, '').slice(0, 100);
                }

            });
        }

        function showSwalError(title, message) {
            Swal.fire({
                icon: 'warning',
                title: title,
                text: message
            });
        }

        function initDataTable() {
            const table = new DataTable('#tableCreditos', {
                processing: true,
                serverSide: true,
                responsive: true,
                ajax: {
                    url: "{{ route('tenant.consultas.reservas.data') }}",
                    data: function(d) {
                        d.search_type = document.getElementById('search_type').value;
                        d.search_input = document.getElementById('search_input').value;
                        d.start_date = document.getElementById('start_date').value;
                        d.end_date = document.getElementById('end_date').value;
                        d.search_estado = document.getElementById('search_estado').value;
                    }
                },
                columns: [{
                        data: null,
                        name: null,
                        searchable: false,
                        orderable: false,
                        render: function(data, type, row, meta) {
                            return '';
                        }
                    },
                    {
                        data: 'id',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if (row.payment_status === 'TOTAL' && row.facturado === 'NO') {
                                return `<input type="checkbox" class="row-checkbox" value="${data}" onclick="selectReservation(${data}, this.checked)">`;
                            } else {
                                return '';
                            }
                        }
                    },
                    {
                        data: 'customer_name',
                        name: 'customer_name'
                    },
                    {
                        data: 'customer_type_document_name',
                        name: 'customer_type_document_name'
                    },

                    {
                        data: 'customer_document_number',
                        name: 'customer_document_number'
                    },
                    {
                        data: 'customer_phone',
                        name: 'customer_phone'
                    },
                    {
                        data: 'field_name',
                        name: 'field_name'
                    },
                    {
                        data: 'schedule',
                        name: 'schedule'
                    },
                    {
                        data: 'date',
                        name: 'date'
                    },
                    {
                        data: 'total_hours',
                        name: 'total_hours'
                    },
                    {
                        data: 'ball',
                        name: 'ball'
                    },
                    {
                        data: 'vest',
                        name: 'vest'
                    },
                    {
                        data: 'dni',
                        name: 'dni'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'facturado',
                        name: 'facturado'
                    },
                ]
            });

            window.dtReservations = table;
        }

        function initExportPDF() {
            const pdfButton = document.getElementById('btnExportPdf');
            if (!pdfButton) return;

            pdfButton.addEventListener('click', handlePDFExport);
        }

        function handlePDFExport() {
            const searchType = document.getElementById('search_type').value;
            const searchInput = document.getElementById('search_input').value.trim();
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (searchType === 'dni' && searchInput.length !== 8) {
                showSwalError('DNI inválido', 'Debe contener exactamente 8 dígitos.');
                return;
            }

            if (searchType === 'ruc' && searchInput.length !== 11) {
                showSwalError('RUC inválido', 'Debe contener exactamente 11 dígitos.');
                return;
            }

            if (searchType === 'nombre' && searchInput.length < 3) {
                showSwalError('Nombre inválido', 'Ingrese al menos 3 caracteres.');
                return;
            }

            const url = new URL("{{ route('tenant.consultas.creditos.pdf') }}", window.location.origin);
            url.searchParams.set('search_type', searchType);
            url.searchParams.set('search_input', searchInput);
            url.searchParams.set('start_date', startDate);
            url.searchParams.set('end_date', endDate);
            url.searchParams.set('search_estado', document.getElementById('search_estado').value);


            window.open(url.toString(), '_blank');
        }
    </script>
@endsection
