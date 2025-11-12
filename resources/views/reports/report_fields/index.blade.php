@extends('layouts.template')

@section('title')
    REPORTE DE CAMPOS
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

    <div class="card">
        @csrf
        <div class="card-header d-flex flex-row justify-content-between">
            <h4 class="card-title">REPORTE DE CAMPOS</h4>
        </div>
        <div class="card-body">
            <div class="row">
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
                    <button class="btn btn-primary" style="margin-right: 10px;" onclick="downloadExcel();">
                        <i class="fa-solid fa-file-excel"></i> EXCEL
                    </button>
                    <button class="btn btn-primary" onclick="downloadPdf();">
                        <i class="fa-solid fa-file-pdf"></i> PDF
                    </button>
                </div>
                <div class="col-12">
                   @include('reports.report_fields.tables.tbl_report_fields')
                </div>
            </div>
          
        </div>
    </div>
@endsection


@section('js')
<script>

    let dtReportFields =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        events();
    })

    function events(){
        startDataTableReportFields();
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

    function startDataTableReportFields(){
        const urlGetReportFields = '{{ route('tenant.reportes.reporte_campo.getReporteCampos') }}';

        dtReportFields  =   new DataTable('#tbl_report_fields',{
            responsive:true,
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetReportFields,
                type: 'GET',
                data:function(d){
                    d.date_start    =   document.querySelector('#date_start').value;
                    d.date_end      =   document.querySelector('#date_end').value;
                }
            },
            columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'date', name: 'date' },
                { data: 'field_name', name: 'field_name' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'schedule_description', name: 'schedule_description' },
                { data: 'amount', name: 'amount' },
                { data: 'status', name: 'status' },
                {
                    data: null,
                    name: 'static_column',
                    render: function(data, type, row, meta) {


                        if (!row.reservation_document_id && row.status === 'ALQUILADO') {
                            const id    =   row.id;
                            const urlGenerateDocumentCreate = "{{ route('tenant.reportes.reporte_campo.generarDocumento', ['id' => '__ID__']) }}".replace('__ID__', id);
                            return `<a class="btn btn-sm btn-primary generate-btn" data-id="${row.id}" href="${urlGenerateDocumentCreate}">Generar</a>`;
                        }  
                        
                        if(row.reservation_document_id){
                            return row.reservation_document_nro;
                        }else{
                            return '';
                        } 

                    },
                    orderable: false, 
                    searchable: false 
                },
                {
                    data: null,
                    name: 'static_column',
                    render: function(data, type, row, meta) {

                        let actions =   `<div class="btn-group">
                                            <button class="btn btn-danger btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-grip"></i>
                                            </button>
                                            <ul class="dropdown-menu">
                                            `;

                        if(row.reservation_document_id){
                            const urlPdfComprobante =  "{{ route('tenant.reportes.reporte_campo.pdf_voucher', ':id')}}".replace(':id',row.reservation_document_id);

                            actions +=  `<li>
                                            <a target="_blank" class="dropdown-item" href="${urlPdfComprobante}">
                                                <i class="fa-solid fa-file-pdf"></i> Pdf comprobante
                                            </a>
                                        </li>`;
                        }

                        actions +=  `</ul></div>`;

                        return actions;
                        
                    },
                    orderable: false, 
                    searchable: false 
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


    function goToSaleCreate(){
        const route =   @json(route('tenant.ventas.comprobante_venta.create'));
        window.location.href = route;
    }

    function filterDataTable(){
        dtReportFields.ajax.reload();
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
        
        const url = @json(route('tenant.reportes.reporte_campo.excel'));
    
        const params = {
            date_start: document.querySelector('#date_start').value,
            date_end: document.querySelector('#date_end').value
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.location.href = finalUrl;

    }

    function downloadPdf(){
        
        const url = @json(route('tenant.reportes.reporte_campo.pdf'));
    
        const params = {
            date_start: document.querySelector('#date_start').value,
            date_end: document.querySelector('#date_end').value
        };

        const queryString = new URLSearchParams(params).toString();

        const finalUrl = `${url}?${queryString}`;
        window.open(finalUrl, '_blank'); 

    }
  
    

</script> 
<script src="{{asset('assets/js/utils.js')}}"></script>
<script src="{{ asset('assets/js/extended-ui-perfect-scrollbar.js') }}"></script>
@endsection
