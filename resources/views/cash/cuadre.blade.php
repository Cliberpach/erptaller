@extends('layouts.template')

@section('title')
    Caja | Cuadre
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{asset('assets/css/cuadre.css')}}">
@endsection

@section('content')
        <!-- Elemento de superposición -->
        @if(session('error'))
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
            <div class="card-header d-flex flex-row justify-content-between">
                <h4 class="card-title">Lista de cajas</h4>
                
                <div class="input-group-append">
                    
                    <button  type="button" data-bs-whatever="Crear nueva caja" class="btn btn-primary btnAbrirCaja" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
                        <table id="miTabla" style="width:100%" class="table table-hover" > 
                            <thead>
                                <tr>
                                    <th data-priority="1" >CAJA</th>
                                    <th >CANTIDAD INICIAL</th>
                                    <th data-priority="2" >FECHA APERTURA</th>
                                    <th >FECHA CIERRE</th>
                                    <th >CANTIDAD CIERRE</th>
                                    <th >VENTA DEL DIA</th>
                                    <th >ACCIONES</th>
                                </tr>
                            </thead>
                            <tbody class="body-table">
                             

                            </tbody>
                        </table>
                    </div>
                </div>
            
            </div>
        </div>
    

    <!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="model-header-btn">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-header-titulo">
                   
                    <i class="fas fa-cogs" style="font-size: 85px; color: #96a9ca;"></i>
                    <h5 class="modal-title" id="exampleModalLabel">Caja</h5>
                    <p style="font-size: 11px">Apertura de caja</p>
                </div>
            </div>
            <hr>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row"> 
                            <div class="col-md-12 colCaja">
                                    <div class="form-group">
                                        <label for="selectCajas" class="form-label">Cajas Disponibles</label>
                                        <select  class="form-select selectCajas" id="selectCajas" aria-label="Default select example">
                                            <option value="0" selected>SELECCIONAR</option>
                                            @foreach($cashList as $cash) 
                                                <option value="{{ $cash->id }}">{{ $cash->name }}</option>
                                            @endforeach
                                        </select>
                                        <p hidden class="msgError errorCaja"></p> 
                                    </div>
                                    <div style="margin-top:11px;" class="form-group pt-2">
                                        <label for="selectTurnos" class="form-label">Turno</label>
                                        <select class="form-select selectTurnos" id="selectTurnos" aria-label="Default select example">
                                            <option value="0" selected>SELECCIONAR</option>
                                            @foreach($shiftList as $shift)
                                                <option value="{{ $shift->id }}">{{ $shift->time }}</option>
                                            @endforeach
                                        </select>
                                        <p hidden class="msgError errorTurno"></p> 
                                    </div>
                                    <div style="margin-top:11px;" class="form-group pt-2">
                                        <label for="inputSaldo" class="form-label">Saldo inicial</label>
                                        <div  class="input-group mb-1">
                                            <span class="input-group-text">S/.</span>
                                            <input type="number" class="form-control inputSaldo" aria-label="Amount (to the nearest dollar)" id="inputSaldo">
                                            
                                        </div>
                                        <p hidden class="msgError errorSaldo"></p> 
                                    </div>
                                       
                            </div> 
                    </div>
                </div>
            </div>
            <hr>
            <div class="modal-footer">
            <div class="row footer-row">
                <div class="col-5 col-info">
                    <i class="fas fa-info-circle"></i>
                    <p>Los campos marcados con asterisco (*) son obligatorios.</p>
                </div>
                <div class="col-7 col-botones">
                    <button  type="button" class="btn btn-secondary btnCancelar" data-bs-dismiss="modal">
                        <i class="fas fa-window-close"></i>Cancelar
                    </button>
                    <button type="button" data-bs-dismiss="modal" 
                    class="btn btn-primary btnGuardar">
                        <i class="fas fa-save"></i>Guardar
                    </button>
                </div>
            
            </div>
            </div>
        </div>
    </div>
    <input type="button" value="">
</div>

<script>
    
    var cashBookList = @json($cashBookList); 
    var columns= [
            { data: 'name_cash' },
            { data: 'initial_amount' },
            { data: 'initial_date' },
            { data: 'final_date' },
            { data: 'closing_amount' },
            { data: 'sale_day' },
            {
                data: 'id',
                render: function (data, type, row) {

                    const pdfUrl = `{{ route('tenant.cajas.venta', ':id') }}`.replace(':id', data);

                        if(row.status_cajaunica=="open"){ //logré arreglar el problema de los botones, para poder repararlo en un futuro
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
                        if(row.status_cajaunica=="close"){
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

    <script src="{{asset('assets/js/petty-cash-book.js')}}" type="module"></script>
@endsection