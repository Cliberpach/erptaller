@extends('layouts.template')

@section('title')
    Consultas de Créditos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
<div class="container mt-4">
    <h4 class="mb-4">Consultas de Créditos</h4>

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
                    <label for="search_estado" class="form-label fw-bold">Estado</label>
                    <select class="form-select" id="search_estado">
                        <option value="PENDIENTE" selected>PENDIENTE</option>
                        <option value="PAGADO">PAGADO</option>
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
                @include('consultas.creditos.tables.table_creditos')

                
                
            </div>

            <div class="d-flex justify-content-between mt-3" id="accionesPago">
                <button type="button" class="btn btn-outline-secondary" id="btnSeleccionarTodos">
                    <i class="bx bx-check-square me-1"></i> Seleccionar Todos
                </button>
            
                <button type="button" class="btn btn-success" id="btnPagarSeleccionados">
                    <i class="bx bx-dollar-circle me-1"></i> Pagar Seleccionados
                </button>
            </div>
            
            
        </div>
    </div>

    @include('consultas.creditos.modals.generar_documento')

</div>
@endsection


@section('js')

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initDefaultDates();
        initSeleccionarTodos();
        initSearchTypeEvents();
        initInputValidation();
        initSearchForm();
        document.getElementById('search_estado').addEventListener('change', handlePagoActionsVisibility); 
        initExportPDF();
        initExportExcel();
        initPayCredits();
        initDataTable();
    });

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


    function initSeleccionarTodos() {
        const btnToggle = document.getElementById('btnSeleccionarTodos');

        let allSelected = false;

        btnToggle.addEventListener('click', () => {
            const checkboxes = document.querySelectorAll('.row-checkbox');

            allSelected = !allSelected;

            checkboxes.forEach(cb => {
                cb.checked = allSelected;
            });

            btnToggle.innerHTML = allSelected
                ? `<i class="bx bx-x-square me-1"></i> Deseleccionar Todos`
                : `<i class="bx bx-check-square me-1"></i> Seleccionar Todos`;
        });
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
            dni: { max: 8, placeholder: 'Ingrese DNI (8 dígitos)', label: 'DNI' },
            ruc: { max: 11, placeholder: 'Ingrese RUC (11 dígitos)', label: 'RUC' },
            nombre: { max: 100, placeholder: 'Ingrese Nombre', label: 'Nombre' },
            razon_social: { max: 100, placeholder: 'Ingrese Razón Social', label: 'Razón Social' }
        };

        const selected = config[type] || { max: 100, placeholder: 'Ingrese valor', label: 'Filtro' };

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


    function initSearchForm() {
        const form = document.getElementById('searchForm');
        const type = document.getElementById('search_type');
        const input = document.getElementById('search_input');

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const value = input.value.trim();

            if (type.value === 'dni' && value.length !== 8) {
                showSwalError('DNI inválido', 'Debe contener exactamente 8 dígitos.');
                return;
            }

            if (type.value === 'ruc' && value.length !== 11) {
                showSwalError('RUC inválido', 'Debe contener exactamente 11 dígitos.');
                return;
            }

            if ((type.value === 'nombre' || type.value === 'razon_social') && value.length < 3) {
                showSwalError('Campo inválido', 'Ingrese al menos 3 caracteres para buscar.');
                return;
            }


            if (window.creditosTable) {
                window.creditosTable.ajax.reload();
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
            ajax: {
                url: "{{ route('tenant.consultas.creditos.data') }}",
                data: function (d) {
                    d.search_type = document.getElementById('search_type').value;
                    d.search_input = document.getElementById('search_input').value;
                    d.start_date = document.getElementById('start_date').value;
                    d.end_date = document.getElementById('end_date').value;
                    d.search_estado = document.getElementById('search_estado').value;
                }
            },
            columns: [
                {
                    data: 'id',
                    name: 'checkbox',
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row) {
                        return `<input type="checkbox" class="row-checkbox" value="${data}">`;
                    }
                },
                { data: 'cliente', name: 'cliente' },
                { data: 'documento', name: 'documento' },
                { data: 'customer_phone', name: 'customer_phone' },
                { data: 'field_name', name: 'field_name' },
                { data: 'horario', name: 'horario' },
                { data: 'date', name: 'date' },
                { data: 'total_hours', name: 'total_hours' },
                { data: 'ball', name: 'ball' },
                { data: 'vest', name: 'vest' },
                { data: 'dni', name: 'dni' },
                { data: 'amount', name: 'amount' },
                { data: 'estado', name: 'estado' },
                { data: 'facturado', name: 'facturado' },
            ]
        });
    
        window.creditosTable = table;
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


    function initPayCredits() {
    const payButton = document.getElementById('btnPagarSeleccionados');
    const modal = new bootstrap.Modal(document.getElementById('modalGenerarDocumento'));
    const detallesContainer = document.getElementById('detalleCreditosSeleccionados');
    const totalPagarSpan = document.getElementById('totalPagar');

    payButton.addEventListener('click', () => {
        const selectedCheckboxes = document.querySelectorAll('.row-checkbox:checked');
        detallesContainer.innerHTML = '';
        let total = 0;

        if (selectedCheckboxes.length === 0) {
            showSwalError('Sin selección', 'Seleccione al menos un crédito para pagar.');
            return;
        }

        const grupos = {};

        selectedCheckboxes.forEach(cb => {
            const row = cb.closest('tr');
            const cliente = row.querySelector('td:nth-child(2)').textContent.trim();
            const monto = parseFloat(row.querySelector('td:nth-child(12)').textContent.trim());
            total += monto;

            const clave = `${cliente}|${monto}`;

            if (!grupos[clave]) {
                grupos[clave] = { cliente, monto, cantidad: 1 };
            } else {
                grupos[clave].cantidad += 1;
            }
        });

        Object.values(grupos).forEach(grupo => {
            const montoTotal = grupo.monto * grupo.cantidad;
            const item = document.createElement('li');
            item.className = 'list-group-item d-flex justify-content-between align-items-center';

            item.innerHTML = `
                <div>
                    <strong>${grupo.cliente}</strong><br>
                    <span class="text-muted">Cantidad: ${grupo.cantidad}</span><br>
                    <span class="text-muted">Precio unitario: S/ ${grupo.monto.toFixed(2)}</span>
                </div>
                <span class="badge bg-primary rounded-pill">S/ ${montoTotal.toFixed(2)}</span>
            `;

            detallesContainer.appendChild(item);
        });

        const igvRate = 0.18;
        const subtotal = total / (1 + igvRate);
        const igv = total - subtotal;

        document.getElementById('subtotalOperacion').textContent = `S/ ${subtotal.toFixed(2)}`;
        document.getElementById('igvOperacion').textContent = `S/ ${igv.toFixed(2)}`;
        totalPagarSpan.textContent = total.toFixed(2);

        modal.show();
    });

    // Manejo tipo de comprobante
    const tipoComprobante = document.getElementById('tipoComprobante');
    const documentLabel = document.getElementById('documentLabel');
    const documentInput = document.getElementById('documentInput');
    const nombreCliente = document.getElementById('nombreCliente');

    tipoComprobante.addEventListener('change', () => {
        if (tipoComprobante.value === 'boleta') {
            documentLabel.textContent = 'DNI';
            documentInput.placeholder = 'Ingrese DNI';
            documentInput.maxLength = 8;
        } else {
            documentLabel.textContent = 'RUC';
            documentInput.placeholder = 'Ingrese RUC';
            documentInput.maxLength = 11;
        }
        documentInput.value = '';
        nombreCliente.value = '';
    });

    // Botón de búsqueda de cliente (acción futura)
    const btnBuscarCliente = document.getElementById('btnBuscarCliente');
    btnBuscarCliente.addEventListener('click', () => {
        // Lógica de búsqueda la implementarás tú
        console.log('Buscar cliente: ', documentInput.value);
    });

    // Validación antes de generar documento
    document.getElementById('btnConfirmarPago').addEventListener('click', () => {
        const tipo = tipoComprobante.value;
        const docValue = documentInput.value.trim();
        const isDni = tipo === 'boleta';
        const expectedLength = isDni ? 8 : 11;

        if (docValue.length !== expectedLength || !/^\d+$/.test(docValue)) {
            showSwalError(
                isDni ? 'DNI inválido' : 'RUC inválido',
                `Debe ingresar un ${isDni ? 'DNI' : 'RUC'} válido de ${expectedLength} dígitos.`
            );
            return;
        }

        // Aquí se envía la solicitud al servidor (sin cambios)
    });
}


</script>



    
    
@endsection

