@extends('layouts.template')

@section('title')
    COMPROBANTES RESERVAS
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

    <div class="card">
        @csrf
        <div class="card-header d-flex flex-row justify-content-between">
            <h4 class="card-title">COMPROBANTES RESERVAS</h4>
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
                    <button class="btn btn-primary" style="margin-right: 10px;" onclick="downloadExcel();">EXCEL</button>
                    <button class="btn btn-primary" onclick="downloadPdf();">PDF</button>
                </div>
                <div class="col-12">
                   @include('reports.reservation_documents.tables.tbl_reservation_documents')
                </div>
            </div>
          
        </div>
    </div>
@endsection


@section('js')
<script>

    let dtReservationDocuments =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        events();
    })

    function events(){
        startDataTableReservationDocuments();
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

    function startDataTableReservationDocuments(){
        const urlGetReservationDocuments = '{{ route('tenant.reportes.comprobantes_reservas.getReservationDocuments') }}';

        dtReservationDocuments  =   new DataTable('#tbl_reservation_documents',{
            responsive:true,
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetReservationDocuments,
                type: 'GET',
                data:function(d){
                    d.date_start    =   document.querySelector('#date_start').value;
                    d.date_end      =   document.querySelector('#date_end').value;
                }
            },
            order: [[0, 'desc']],
            columns: [
              
                { data: 'id', name: 'id' },
                { data: 'fecha_registro', name: 'fecha_registro' },
                { data: 'customer_name', name: 'customer_name' },
                { data: 'doc', name: 'doc' },
                { data: 'total', name: 'total' },
                {
                    data: null, 
                    render: function(data, type, row) {
                        
                        let badge_class =   '';

                        if(data.estado === 'PENDIENTE'){
                            badge_class =   'danger';
                        }
                        if(data.estado === 'ENVIADO'){
                            badge_class =   'warning';
                        }
                        if(data.estado === 'ACEPTADO'){
                            badge_class =   'primary';
                        }
                        if(data.estado === 'RECHAZADO'){
                            badge_class =   'dark';
                        }

                        return `<span class="badge bg-${badge_class}">${data.estado}</span>`;
                    },
                    name: 'actions', 
                    orderable: false, 
                    searchable: false 
                },
                {
                    data: null, 
                    render: function(data, type, row) {

                        const urlDownloadXml = "{{ route('tenant.reportes.comprobantes_reservas.downloadXml', ':id') }}".replace(':id', data.id);
                        const urlDownloadCdr = "{{ route('tenant.reportes.comprobantes_reservas.downloadCdr', ':id') }}".replace(':id', data.id);
                        const urlPdf         =  "{{ route('tenant.reportes.comprobantes_reservas.pdf_voucher', ':id')}}".replace(':id',data.id);
                        
                        let descargas = `<div style="display: flex; justify-content: flex-start; gap: 10px; flex-wrap: nowrap;">`;

                        descargas   +=  `<a target="_blank" class="btn btn-danger" style="color:white; max-width: 150px; flex-shrink: 0;" href="${urlPdf}">
                                            <i class="fa-solid fa-file-pdf"></i> PDF   
                                        </a>`;

                        if(data.ruta_xml){
                            const asset_route   =   @json(asset(''));
                            descargas   +=  `<a class="btn btn-success" style="color:white; max-width: 150px; flex-shrink: 0;" href="${urlDownloadXml}" >
                                                <i class="fa-solid fa-file-excel"></i> XML   
                                            </a>`;
                        }

                        if(data.ruta_cdr){
                            const asset_route   =   @json(asset(''));
                            descargas   +=  `<a class="btn btn-primary" style="color:white; max-width: 150px; flex-shrink: 0;" href="${urlDownloadCdr}">
                                                <i class="fa-solid fa-book"></i> CDR   
                                            </a>`;
                        }

                        descargas   +=  `</div>`;

                        return descargas;
                    },
                    name: 'actions', 
                    orderable: false, 
                    searchable: false 
                },
                {
                    data: null, 
                    render: function(data, type, row) {
                        
                        let acciones    =   ` <div class="btn-group">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa-solid fa-bars-staggered"></i>
                                            </button>
                                            <ul class="dropdown-menu">`;

                        if( data.type_sale_code === '3' || data.type_sale_code === '1'){

                            if (data.estado === 'PENDIENTE' || data.estado === 'RECHAZADO') {
                                acciones    +=  `<li>
                                                <a class="dropdown-item" href="javascript:void(0);" onclick="sendSunat(${data.id})">
                                                    <i class="fa-solid fa-paper-plane"></i> Sunat
                                                </a>
                                            </li>`; 
                            }
                            
                        }  
                        
                        acciones += `</ul></div>`;

                        
                        return acciones;
                    },
                    name: 'actions', 
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
        dtReservationDocuments.ajax.reload();
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

    function sendSunat(sale_document_id){

        const sale_document =   getRowById(dtReservationDocuments,sale_document_id);

        let message =   `Enviar el documento: ${sale_document.serie}-${sale_document.correlative} a Sunat?`;

        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: message,
        text: "OPERACIÓN NO REVERSIBLE!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, enviar!",
        cancelButtonText: "No, cancelar!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {
        
            Swal.fire({
                title: `Documento de venta: ${sale_document.serie}-${sale_document.correlative}`,
                html: "ENVIANDO A SUNAT...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {
                toastr.clear();
                const token                   =   document.querySelector('input[name="_token"]').value;
                
                const formData                =   new FormData();
                const urlInvoice              =   @json(route('tenant.reportes.comprobantes_reservas.send_sunat'));

                formData.append('sale_document_id',sale_document_id);

                const response  =   await fetch(urlInvoice, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();

                Swal.close();

                if(response.status === 422){
                    if('errors' in res){
                        //pintarErroresValidacion(res.errors);
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){
                    dtReservationDocuments.ajax.reload(null, false);
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ENVIAR A SUNAT');
            }

        } else if (
            /* Read more about handling dismissals below */
            result.dismiss === Swal.DismissReason.cancel
        ) {
            swalWithBootstrapButtons.fire({
            title: "Operación cancelada",
            text: "No se realizaron acciones",
            icon: "error"
            });
        }
        });
        }
  
    

</script> 
<script src="{{asset('assets/js/utils.js')}}"></script>
@endsection
