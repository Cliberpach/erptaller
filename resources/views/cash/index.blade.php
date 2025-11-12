@extends('layouts.template')

@section('title')
    Caja
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')
    <!-- Elemento de superposición -->
    <div id="overlay" class="overlay"></div>


    <div class="card">
        <div class="card-header d-flex flex-row justify-content-between">
            <h4 class="card-title">LISTA DE CAJAS</h4>

            <div class="input-group-append">
                <button  type="button" data-bs-whatever="Nueva caja" class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
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
                    <div class="table-responsive">
                        @include('cash.tables.tbl_cash_list')
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">   </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <hr>
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                                <div class="col-md-12">

                                        <p>Nombre <span>*</span></p>
                                        <input name="nombre"  type="text" class="form-control inputName" placeholder="Nombre de la categoría" aria-label="Cajaname" aria-describedby="basic-addon1">

                                        <p hidden class="msgError error">error</p>
                                </div>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="modal-footer">
                        <div class="col-info">
                            <i class="fas fa-info-circle"></i>
                            <p style="margin:0">Los campos marcados con asterisco (*) son obligatorios.</p>
                        </div>
                        <div class="col-buttons">
                            <button  type="button" class="btn btn-secondary btn-cancel" data-bs-dismiss="modal">
                                <i style="margin-right: 3px;" class="fas fa-window-close"></i>Cancelar
                            </button>
                            <button type="button"
                            class="btn btn-primary btn-save" data-bs-dismiss="modal">
                                <i style="margin-right: 3px;" class="fas fa-save"></i>Guardar
                            </button>
                        </div>
                </div>
            </div>
        </div>
    </div>


@endsection


@section('js')
    <script>
        var cashList = @json($cashList);
        var columns= [
            { data: 'id' },
            { data: 'name' },
            { data: 'created_at' },
            {
            data: 'id',
            render: function (data, type, row) {

                return   `
                <button data-id="${data}" type="button" data-bs-whatever="Editar caja" class="btn btn-warning btn-edit" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <i data-id="${data}" class="fas fa-edit btn-edit"></i>
                </button>
                <button data-id="${data}" type="button" class="btn btn-danger btn-delete">
                    <i data-id="${data}" class="fas fa-trash-alt btn-delete"></i>
                </button>
                `;
            }
            }
        ]
    </script>
    <script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
    <script src="{{asset('assets/js/petty-cash.js')}}" type="module"></script>
@endsection
