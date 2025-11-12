@extends('layouts.template')

@section('title')
    NOTAS SALIDA
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

    @include('utils.spinners.spinner_1')
    @include('inventory.note_release.modals.mdl_show')

    <div class="card">
        @csrf
        <div class="card-header d-flex flex-row justify-content-between">
            <h4 class="card-title">NOTAS DE SALIDA</h4>
            
            <div class="input-group-append">
                <button onclick="goToNoteReleaseCreate()"  type="button" data-bs-whatever="Nueva caja" class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <div class="lign-items-center d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2"> NUEVO</p>
                    </div>
                </button>  
            </div>
          
        </div>
        <div class="card-body">
            <div class="row">
                {{-- <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <label for="product_id" style="font-weight:bold;">PRODUCTO</label>
                    <select data-placeholder="Seleccionar" id="product_id" class="form-select select2_form" aria-label="Default select example" onchange="filterDataTable()">
                        <option value=""></option>
                        @foreach ($products as $product)
                            <option value="{{$product->id}}">{{$product->name}}</option>
                        @endforeach
                    </select>
                </div> --}}
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <label for="date_start" style="font-weight:bold;">FECHA INICIO</label>
                    <input type="date" class="form-control" id="date_start" onchange="changeDateStart(this.value)">
                </div>
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <label for="date_end" style="font-weight:bold;">FECHA FIN</label>
                    <input type="date" class="form-control" id="date_end" onchange="changeDateEnd(this.value)">
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                   @include('inventory.note_income.tables.table_list_note_income')
                </div>
            </div>
          
        </div>
    </div>
@endsection

@section('js')
<script>
let dtNoteIncome =   null;

document.addEventListener('DOMContentLoaded',()=>{
    events();
})

function events(){
    paintAlerts();
    startDataTableNoteRelease();
    loadSelect2();
}

function loadSelect2(){
    $( '.select2_form' ).select2( {
        theme: "bootstrap-5",
        width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
        placeholder: $( this ).data( 'placeholder' ),
        allowClear: true 
    } );
}

function paintAlerts(){
    @if(Session::has('note_release_success'))
        toastr.success('{{ Session::get('note_release_success') }}');
    @endif
}

function startDataTableNoteRelease(){
    const urlGetNoteIncome = '{{ route('tenant.inventarios.nota_salida.getNotesRelease') }}';

    dtNoteIncome  =   new DataTable('#tbl_list_note_income',{
        responsive:true,
        serverSide: true,
        processing: true,
        ajax: {
            url: urlGetNoteIncome,
            type: 'GET',
            data:function(d){
                // d.product_id    =   document.querySelector('#product_id').value;
                d.date_start    =   document.querySelector('#date_start').value;
                d.date_end      =   document.querySelector('#date_end').value;
            }
        },
        order: [[0, 'desc']],
        columns: [
            { data: 'id', name: 'id' }, 
            { data: 'created_at', name: 'created_at' },
            { data: 'user_recorder_name', name: 'user_recorder_name' },
            { data: 'observation', name: 'observation' },
            { 
                data: null, 
                name: null,
                render: function(data, type, row, meta) {
                    return `
                    <div class="btn-group">
                    <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bars"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="javascript:void(0);" onclick="openMdlShowNoteRelease(${data.id});"><i class="fas fa-eye"></i> Ver</a></li>
                    </ul>
                    </div>`;
                }
            }
        ],
        pageLength: 25,
        lengthChange: false,
        language: {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "next": "Siguiente",
                "previous": "Anterior"
            },
            "loadingRecords": "Cargando...",
            "processing": "Procesando...",
            "emptyTable": "No hay datos disponibles en la tabla",
            "aria": {
                "sortAscending": ": activar para ordenar la columna de manera ascendente",
                "sortDescending": ": activar para ordenar la columna de manera descendente"
            }
        }
    });
}


function goToNoteReleaseCreate(){
    const route =   @json(route('tenant.inventarios.nota_salida.create'));
    window.location.href = route;
}

function filterDataTable(){
    dtNoteIncome.ajax.reload();
}

function changeDateStart(date_start){

    toatr.clear();
    const date_end  =   document.querySelector('#date_end').value;

    if(date_start > date_end && date_end){
        document.querySelector('#date_start').value  =   '';
        toastr.error('LA FECHA DE INICIO DEBE SER MENOR IGUAL A LA FECHA FINAL!!');
        return;
    }

    filterDataTable();

}

function changeDateEnd(date_end){
    
    toatr.clear();
    const date_start  =   document.querySelector('#date_start').value;

    if(date_end < date_start && date_start){
        document.querySelector('#date_end').value  =   '';
        toastr.error('LA FECHA FINAL DEBE SER MAYOR IGUAL A LA FECHA INICIAL!!');
        return;
    }

    filterDataTable();

}



</script> 
<script src="{{asset('assets/js/utils.js')}}"></script>
<script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
@endsection