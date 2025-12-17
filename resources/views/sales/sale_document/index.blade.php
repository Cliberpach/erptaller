@extends('layouts.template')

@section('title')
    Ventas
@endsection

@section('css')
@endsection

@section('content')
    <div class="card overflow-hidden">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h6 class="card-title mb-0">LISTA DE VENTAS</h6>
            <div class="input-group-append">
                <button onclick="goToSaleCreate()" type="button" data-bs-whatever="Nueva caja"
                    class="btn btn-primary btn-add-new" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    <div class="lign-items-center d-flex align-items-center">
                        <i class="fas fa-plus pe-1"></i>
                        <p class="mb-0 ml-2"> NUEVO</p>
                    </div>
                </button>
            </div>
        </div>
        <div class="card-body p-0 pb-2">
            @include('sales.sale_document.tables.tbl_list_sales')
        </div>
    </div>
@endsection

@section('js')
<script>

    let dtSales =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        events();
    })

    function events(){
        startDataTableSales();
    }

    function startDataTableSales(){
        const urlGetSales = '{{ route('tenant.ventas.comprobante_venta.getSales') }}';

        dtSales  =   new DataTable('#tbl_list_sales',{
            responsive:true,
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetSales,
                type: 'GET'
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

                        const urlDownloadXml = "{{ route('tenant.ventas.comprobante_venta.downloadXml', ':id') }}".replace(':id', data.id);
                        const urlDownloadCdr = "{{ route('tenant.ventas.comprobante_venta.downloadCdr', ':id') }}".replace(':id', data.id);
                        const urlPdf         =  "{{ route('tenant.ventas.comprobante_venta.pdf_voucher', ':id')}}".replace(':id',data.id);

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

                        let acciones    =   `<div class="btn-group float-end">
                                                <button
                                                    class="btn btn-white btn-sm btn-shadow btn-icon waves-effect dropdown-toggle"
                                                    type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fi fi-rr-menu-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">`;

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

    function sendSunat(sale_document_id){

        const sale_document =   getRowById(dtSales,sale_document_id);

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
                const urlInvoice              =   @json(route('tenant.ventas.comprobante_venta.send_sunat'));

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
                    dtSales.ajax.reload(null, false);
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                //     window.location.href    =   sale_index;
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR VENTA');
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
