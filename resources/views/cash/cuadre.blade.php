@extends('layouts.template')

@section('title')
    Caja | Cuadre
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/cuadre.css') }}">
@endsection

@section('content')
    <!-- Elemento de superposición -->
    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: "{{ session('error') }}",
                confirmButtonText: 'Aceptar'
            });
        </script>
    @endif
    <div id="overlay" class="overlay"></div>


    {{-- <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1">@</span>
            <input type="date" class="form-control" placeholder="Username" aria-label="Username" aria-describedby="basic-addon1">
        </div> --}}



    <div class="card">
        <div class="card-header d-flex justify-content-between flex-row">
            <h4 class="card-title">Lista de cajas</h4>

            <div class="input-group-append">

                <button type="button" data-bs-whatever="Crear nueva caja" class="btn btn-primary btnAbrirCaja"
                    data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <div class="lign-items-center d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2">Añadir nuevo</p>
                    </div>
                </button>
            </div>
        </div>
        <div class="card-body">

            <div class="row">
                <div class="col">

                </div>
            </div>

        </div>
    </div>
    <script>
        var cashBookList = @json($cashBookList);
        var columns = [{
                data: 'name_cash'
            },
            {
                data: 'initial_amount'
            },
            {
                data: 'initial_date'
            },
            {
                data: 'final_date'
            },
            {
                data: 'closing_amount'
            },
            {
                data: 'sale_day'
            },
            {
                data: 'id',
                render: function(data, type, row) {

                    const pdfUrl = `{{ route('tenant.cajas.venta', ':id') }}`.replace(':id', data);

                    if (row.status_cajaunica ==
                        "open") { //logré arreglar el problema de los botones, para poder repararlo en un futuro
                        return `
                            <div class="acciones">
                            <button data-id="${data}" data-petty-cash-id="${row.petty_cash_id}" type="button" class="btn btn-warning btnCerrar eventCerrar">
                            <i class="fas fa-lock eventCerrar" style="color: #ffffff;"></i>
                            CERRAR</button>
                            <a target="_blank" href="${pdfUrl}">
                                <button type="button" class="btn btn-danger btnPdf">
                                <i class="far fa-file-pdf"></i>
                                </button>
                            </a>
                            </div>
                            `;
                    }
                    if (row.status_cajaunica == "close") {
                        return `
                            <div class="acciones">
                            <button type="button" class="btn btn-warning btnCerrar cerrado">
                            <i class="fas fa-check-circle"></i>CERRADA</button>
                            <a href="${pdfUrl}"><button type="button" class="btn btn-danger btnPdf">
                            <i class="far fa-file-pdf"></i>
                            </button></a>
                            </div>
                            `;
                    }
                }
            }
        ];
    </script>
@endsection




@section('js')
    <script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('assets/js/petty-cash-book.js') }}" type="module"></script>
@endsection
