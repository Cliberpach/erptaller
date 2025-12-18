@extends('layouts.template')

@section('title')
    Movimientos de Caja
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <style>
        .my-swal {
            z-index: 3000 !important;
        }
    </style>
@endsection

@section('content')
    @include('cash.petty-cash-book.modals.mdl_open_cash')
    @include('cash.petty-cash-book.modals.mdl_close_cash')
    <div class="card overflow-hidden">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">MOVIMIENTOS DE CAJA</h6>
            <div class="d-flex flex-wrap gap-2">
                <a onclick="openMdlOpenCash()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> NUEVO
                </a>
            </div>
        </div>
        <div class="card-body p-0 pb-2">
            @include('cash.petty-cash-book.tables.tbl_list')
        </div>
    </div>
@endsection

@section('js')
    <script>
        let dtCash = null;

        document.addEventListener('DOMContentLoaded', () => {
            iniciarDtCash();
            iniciarTomSelect();
            events();
        })

        function events() {
            eventsMdlOpenCash();
            eventsMdlCloseCash();
        }

        function iniciarDtCash() {
            dtCash = new DataTable('#dt-cash-books', {
                "processing": true,
                "ajax": '{{ route('tenant.cajas.getCashBooks') }}',
                "columns": [{
                        data: 'id',
                        className: "text-center",
                        "visible": false,
                        "searchable": false
                    },
                    {
                        data: 'code',
                        name: 'code',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'petty_cash_name',
                        name: 'c.petty_cash_name',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'initial_amount',
                        name: 'c.initial_amount',
                        searchable: false,
                        orderable: false,
                        className: "text-center"
                    },
                    {
                        data: 'initial_date',
                        name: 'c.initial_date',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'final_date',
                        name: 'c.final_date',
                        searchable: true,
                        orderable: true,
                        className: "text-center"
                    },
                    {
                        data: 'closing_amount',
                        name: 'c.closing_amount',
                        searchable: false,
                        orderable: false,
                        className: "text-center"
                    },
                    {
                        data: 'status',
                        name: 'c.status',
                        searchable: true,
                        orderable: false,
                        className: "text-center",
                        render: function(status) {

                            let badgeClass = '';

                            switch (status) {

                                case 'CERRADO':
                                    badgeClass = 'bg-danger';
                                    break;

                                case 'ABIERTO':
                                    badgeClass = 'bg-primary';
                                    break;
                            }

                            return `<span class="badge ${badgeClass}">${status}</span>`;
                        }
                    },
                    {
                        searchable: false,
                        orderable:false,
                        data: null,
                        className: "text-center",
                        render: function(data, type, row) {

                            const pdfUrl = route('tenant.movimientos_caja.pdf', {
                                id: data.id
                            });

                            const optionCerrar = data.status === 'ABIERTO' ?
                                `
                                    <li>
                                        <button class="dropdown-item text-primary fw-semibold"
                                                onclick="openMdlCloseCash(${data.id})">
                                            <i class="fas fa-lock me-2"></i> Cerrar caja
                                        </button>
                                    </li>
                                ` : '';

                            return `
                                <div class="dropdown">
                                    <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end">

                                        ${optionCerrar}

                                        <li>
                                            <a class="dropdown-item text-danger fw-semibold" target="_blank" href="${pdfUrl}">
                                                <i class="far fa-file-pdf me-2"></i> PDF
                                            </a>
                                        </li>

                                    </ul>
                                </div>
                            `;
                        }
                    }
                ],
                language: {
                    decimal: "",
                    emptyTable: "No hay datos disponibles en la tabla",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros",
                    infoFiltered: "(filtrado de _MAX_ registros totales)",
                    infoPostFix: "",
                    thousands: ",",
                    lengthMenu: "Mostrar _MENU_ registros",
                    loadingRecords: "Cargando...",
                    processing: "Procesando...",
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    paginate: {
                        first: "Primero",
                        last: "Último",
                        next: "Siguiente",
                        previous: "Anterior"
                    },
                    aria: {
                        sortAscending: ": activar para ordenar columna ascendente",
                        sortDescending: ": activar para ordenar columna descendente"
                    },
                    select: {
                        rows: {
                            _: "%d filas seleccionadas",
                            0: "Haz clic en una fila para seleccionarla",
                            1: "1 fila seleccionada"
                        }
                    }
                },
                "order": [
                    [0, "desc"]
                ],
            });

        }

        function iniciarTomSelect() {

            const cashesAvailable = document.getElementById('cash_available_id');
            if (cashesAvailable && !cashesAvailable.tomselect) {
                window.cashesAvailableSelect = new TomSelect(cashesAvailable, {
                    valueField: 'id',
                    labelField: 'name',
                    searchField: ['name'],
                    create: false,
                    placeholder: 'Seleccionar',
                    plugins: ['clear_button'],
                    preload: true,
                    loadThrottle: 1000,
                    load: async function(query, callback) {
                        try {

                            if (!query.length) {
                                query = '';
                            }

                            const url = route('tenant.utils.searchCashAvailable', {
                                search: query
                            });
                            const response = await fetch(url);
                            const json = await response.json();

                            callback(json.data ?? []);
                        } catch (error) {
                            console.error("Error cargando cajas disponibles:", error);
                            callback();
                        }
                    },
                    render: {
                        option: (item, escape) => `
                            <div style="display:flex; align-items:center; gap:6px;">
                                <i class="fas fa-cash-register" style="color:#1e90ff;"></i>
                                <span>${escape(item.name)}</span>
                            </div>
                        `,
                        item: (item, escape) => `
                            <div style="display:flex; align-items:center; gap:6px;">
                                <i class="fas fa-cash-register" style="color:#1e90ff;"></i>
                                <span>${escape(item.name)}</span>
                            </div>
                        `
                    }
                });
            }
        }

        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: 'btn btn-success',
                cancelButton: 'btn btn-danger',
            },
            buttonsStyling: false
        })

        function eliminar(id) {
            const fila = getRowById(dtCash, id);
            const name = fila.name;
            const messageHtml = `
                <div class="text-center" style="font-size: 15px;">
                    <p class="mb-2">
                        <i class="fas fa-tag text-primary me-2"></i>
                        <strong>Nombre:</strong> ${name}
                    </p>
                </div>
            `;

            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success me-2',
                    cancelButton: 'btn btn-danger',
                    actions: 'd-flex justify-content-center gap-2 mt-3'
                },
                buttonsStyling: false // Necesario para que Bootstrap controle el estilo
            });

            swalWithBootstrapButtons.fire({
                title: '¿Desea eliminar la caja?',
                html: messageHtml,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'No, cancelar',
                focusCancel: true,
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando caja...',
                        html: `
                            <div style="display:flex; align-items:center; justify-content:center; flex-direction:column;">
                                <i class="fa fa-spinner fa-spin fa-3x text-primary mb-3"></i>
                                <p style="margin:0; font-weight:600;">Por favor, espere un momento</p>
                            </div>
                        `,
                        allowOutsideClick: false,
                        showConfirmButton: false
                    });

                    try {
                        const res = await axios.delete(route('tenant.cajas.destroy', id));
                        if (res.data.success) {
                            toastr.success(res.data.message, 'OPERACIÓN COMPLETADA');
                            dtCash.ajax.reload();
                        } else {
                            toastr.error(res.data.message, 'ERROR EN EL SERVIDOR');
                        }
                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN ELIMINAR CAJA');
                    } finally {
                        Swal.close();
                    }

                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    swalWithBootstrapButtons.fire({
                        title: 'Cancelado',
                        text: 'La solicitud ha sido cancelada.',
                        icon: 'error',
                        confirmButtonText: 'Entendido',
                        customClass: {
                            confirmButton: 'btn btn-secondary'
                        },
                        buttonsStyling: false
                    });
                }
            });
        }
    </script>
@endsection
