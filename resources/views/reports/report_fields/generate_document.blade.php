@extends('layouts.template')

@section('title')
    GENERAR DOCUMENTO
@endsection

@section('css')
    <link rel="stylesheet" href="{{asset('assets/css/styles.css')}}">
@endsection

@section('content')

    <div class="card">
        @csrf
        <div class="card-header d-flex flex-row justify-content-between">
            <h4 class="card-title">GENERAR DOCUMENTO</h4>
        </div>
        <div class="card-body">
            <div class="row">
               <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
                    <label for="reservation_id" style="font-weight: bold;">N° RESERVA</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-receipt"></i></span>
                        <input value="{{ 'R-' . str_pad($reservation->id, 8, '0', STR_PAD_LEFT) }}" readonly 
                        type="text" id="reservation_id" class="form-control" placeholder="RESERVATION ID" 
                        aria-label="Username" aria-describedby="basic-addon1">                    
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
                    <label for="date" style="font-weight: bold;">FEC RESERVA</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa-solid fa-calendar"></i>
                        </span>
                        <input value="{{ $reservation->date }}" readonly 
                        type="text" id="date" class="form-control" placeholder="date" 
                        aria-label="Username" aria-describedby="basic-addon1">                    
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
                    <label for="schedule_description" style="font-weight: bold;">HORARIO</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa-solid fa-clock"></i>
                        </span>
                        <input value="{{ $reservation->schedule_description }}" readonly 
                        type="text" id="schedule_description" class="form-control" placeholder="schedule_description" 
                        aria-label="Username" aria-describedby="basic-addon1">                    
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
                    <label for="amount" style="font-weight: bold;">MONTO</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa-solid fa-money-bill"></i>
                        </span>
                        <input value="{{ number_format($reservation->amount, 2, '.', '') }}" readonly 
                        type="text" id="amount" class="form-control" placeholder="amount" 
                        aria-label="Username" aria-describedby="basic-addon1">                    
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
                    <label for="customer_name" style="font-weight: bold;">CLIENTE</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa-solid fa-user-tag"></i>
                        </span>
                        <input value="{{ $customer->name }}" readonly 
                        type="text" id="customer_name" class="form-control" placeholder="CUSTOMER_NAME" 
                        aria-label="Username" aria-describedby="basic-addon1">                    
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
                    <label for="phone" style="font-weight: bold;">TELÉFONO</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa-solid fa-phone"></i>
                        </span>
                        <input value="{{ $customer->phone }}" readonly 
                        type="text" id="phone" class="form-control" placeholder="phone" 
                        aria-label="Username" aria-describedby="basic-addon1">                    
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12 mb-3">
                    <label for="field_name" style="font-weight: bold;">CAMPO</label>
                    <div class="input-group">
                        <span class="input-group-text" id="basic-addon1">
                            <i class="fa-solid fa-user-tag"></i>
                        </span>
                        <input value="{{ $reservation->field_name }}" readonly 
                        type="text" id="field_name" class="form-control" placeholder="field_name" 
                        aria-label="Username" aria-describedby="basic-addon1">                    
                    </div>
                </div>
            </div>
            <hr>
            
            @include('reports.report_fields.forms.form_generate_document')
            
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
        loadSelect2();

        document.querySelector('#formGenerateDocument').addEventListener('submit',(e)=>{
            e.preventDefault();
            generateDocument(e.target)
        })
    }

    function loadSelect2(){
        $( '.select2_form' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            allowClear: true 
        } );
    }

    function generateDocument(formGenerateDocument){

        const invoice_name      =   $('#document_invoice').select2('data')[0].text;
        const document_number   =   document.querySelector('#document_number').value;

        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA GENERAR EL COMPROBANTE?",
        text: `${invoice_name} : ${document_number}`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, GENERAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Cargando...',
                html: 'Generando documento...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {

                clearValidationErrors('msgError')

                const token                     =   document.querySelector('input[name="_token"]').value;
                const formData                  =   new FormData(formGenerateDocument);
                const urlGenerateDocument       =   @json(route('tenant.reportes.reporte_campo.generateDocumentStore'));

                formData.append('reservation_id',@json($reservation->id));
                
                const response  =   await fetch(urlGenerateDocument, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();

                if(response.status === 422){
                    if('errors' in res){
                        console.log(res);
                        paintValidationErrors(res.errors, `error`);
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){

                    toastr.success(res.message,'OPERACIÓN COMPLETADA');

                    const url_open_pdf  = "{{ route('tenant.reportes.reporte_campo.pdf_voucher', ['id' => '__id__']) }}".replace('__id__', res.data.sale_id);
                    window.open(url_open_pdf, 'Comprobante SISCOM', 'location=1, status=1, scrollbars=1,width=900, height=600');

                    const index        =   @json(route('tenant.reportes.reporte_campo'));

                    window.location.href    =   index;
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR VENTA');
            }
          

        } else if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.fire({
            title: "OPERACIÓN CANCELADA",
            text: "NO SE REALIZARON ACCIONES",
            icon: "error"
            });
        }
        });
    }

    function goBack(){
        const route =   @json(route('tenant.reportes.reporte_campo'));
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
