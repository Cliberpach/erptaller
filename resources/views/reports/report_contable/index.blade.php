@extends('layouts.template')

@section('title')
    REPORTE CONTABLE
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

    <div class="card">
        @csrf
        <div class="card-header d-flex flex-row justify-content-between">
            <h4 class="card-title">REPORTE CONTABLE</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                    <label for="filter_document" style="font-weight:bold;">DOCUMENTO</label>
                    <select data-placeholder="Seleccionar" id="filter_document" class="form-select select2_form" aria-label="Default select example" onchange="filterDataTable()">
                        <option value=""></option>
                        <option value="VENTAS">VENTAS (FAC,BOL,NOTA CRED)</option>
                        @foreach ($documents as $document)
                            <option value="{{$document->id}}">{{$document->description}}</option>
                        @endforeach
                    </select>
                </div>
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
                <div class="col-12" style="display:flex;justify-content:end;">
                    <button class="btn btn-primary" style="margin-right: 10px;" onclick="downloadExcel();">EXCEL</button>
                    <button class="btn btn-primary" onclick="downloadPdf();">PDF</button>
                </div>
                <div class="col-12">
                    <div class="table-responsive">
                        @include('reports.report_contable.tables.tbl_report_contable')
                    </div>
                </div>
            </div>
          
        </div>
    </div>
@endsection


@section('js')
<script>

    let dtReportContable =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        events();
    })

    function events(){
        startDataTableReportContable();
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

    function startDataTableReportContable(){
        const urlGetReportContable = '{{ route('tenant.reportes.reporte_contable.getReporteContable') }}';

        dtReportContable  =   new DataTable('#tbl_report_contable',{
            responsive:true,
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetReportContable,
                type: 'GET',
                data:function(d){
                    d.date_start        =   document.querySelector('#date_start').value;
                    d.date_end          =   document.querySelector('#date_end').value;
                    d.filter_document   =   document.querySelector('#filter_document').value;
                }
            },
            order: [[4, 'desc']],
            columns: [
                { data: 'customer_name', name: 'customer_name' },
                { data: 'type_sale_name', name: 'type_sale_name' },
                { data: 'document', name: 'document' },
                { data: 'created_at', name: 'created_at' },
                { data: 'subtotal', name: 'subtotal' },
                { data: 'igv_amount', name: 'igv_amount' },
                { data: 'igv_percentage', name: 'igv_percentage' },
                { data: 'total', name: 'total' },
                { data: 'product_name', name: 'product_name' },
                { data: 'category_name', name: 'category_name' },
                { data: 'estado', name: 'estado' }
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


    function goToSaleCreate(){
        const route =   @json(route('tenant.ventas.comprobante_venta.create'));
        window.location.href = route;
    }

    function filterDataTable(){
        dtReportContable.ajax.reload();
    }

    function changeDateStart(date_start){

        toastr.clear();
        const date_end  =   document.querySelector('#date_end').value;

        if(date_start > date_end && date_end){
            document.querySelector('#date_start').value  =   '';
            toastr.error('LA FECHA DE INICIO DEBE SER MENOR IGUAL A LA FECHA FINAL!!');
            return;
        }

        filterDataTable();

    }

    function changeDateEnd(date_end){
        
        toastr.clear();
        const date_start  =   document.querySelector('#date_start').value;

        if(date_end < date_start && date_start){
            document.querySelector('#date_end').value  =   '';
            toastr.error('LA FECHA FINAL DEBE SER MAYOR IGUAL A LA FECHA INICIAL!!');
            return;
        }

        filterDataTable();

    }


    function downloadExcel(){
        
        const url = @json(route('tenant.reportes.reporte_contable.excel'));
    
        const params = {
            date_start: document.querySelector('#date_start').value,
            date_end: document.querySelector('#date_end').value,
            filter_document:document.querySelector('#filter_document').value,
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.location.href = finalUrl;

    }

    function downloadPdf(){
        
        const url = @json(route('tenant.reportes.reporte_contable.pdf'));
    
        const params = {
            date_start: document.querySelector('#date_start').value,
            date_end: document.querySelector('#date_end').value,
            filter_document:document.querySelector('#filter_document').value,
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.open(finalUrl, '_blank'); 

    }
  
    

</script> 
<script src="{{asset('assets/js/utils.js')}}"></script>
@endsection
