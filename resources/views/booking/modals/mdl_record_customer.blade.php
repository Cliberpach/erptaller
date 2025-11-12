<div style="z-index: 99999;" class="modal fade" id="mdlRecordCustomer" tabindex="-1" aria-labelledby="mdlRecordCustomerLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="mdlRecordCustomerLabel">Historial Cliente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    @include('booking.tables.tbl_record_customer')
                </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        </div>
      </div>
    </div>
</div>

<script>
    const parameters    =   {}

    async function openMdlRecordCustomer(hour,today,field_id) {

        mostrarAnimacion1();
        toastr.clear();
        const documentNumber    =   document.querySelector(`#document_number-${hour}-${today}-${field_id}`).value;

        if(!documentNumber){
            toastr.error('DEBE INGRESAR UN N° DE DNI!!');
            ocultarAnimacion1();
            return;
        }

        if(documentNumber.length !== 8){
            toastr.error('DNI DEBE CONTAR CON 8 DÍGITOS EXACTOS!!!');
            ocultarAnimacion1();
            return;
        }

        const res   =   await getRecordCustomer(documentNumber);

        if(res.success){
            if(res.record_customer.length === 0){
                toastr.error('EL CLIENTE NO TIENE HISTORIAL DE RESERVAS!!!');
            }else{
                paintRecordCustomer(res.record_customer);
                $('#mdlRecordCustomer').modal('show');
            }
        }else{
            if('type' in res){
                toastr.error(res.message,'ERROR EN LA PETICIÓN OBTENER HISTORIAL CLIENTE');
            }else{
                toastr.error(res.message,'ERROR EN EL SERVIDOR');
            }
        }
        ocultarAnimacion1();

    }

    function paintRecordCustomer(record_customer){

        const tbody     =   document.querySelector('#tbl_record_customer tbody');
        tbody.innerHTML =   '';

        let rows    =   '';
        record_customer.forEach(item => {
            rows    +=  `<tr>
                            <th scope="row">${item.booking_id}</th>
                            <td>${item.date_booking}</td>
                            <td>${item.schedule}</td>
                            <td>${item.field}</td>
                            <td>${item.status}</td>
                        </tr>`;
        });

        tbody.innerHTML =   rows;

    }

    async function getRecordCustomer(documentNumber){
        try {
            
            const token             =   document.querySelector('input[name="_token"]').value;
            const urlRecordCustomer =  `/api/customer_record/${documentNumber}`;

            const response  =   await fetch(urlRecordCustomer, {
                                        method: 'GET',
                                        headers: {
                                            'X-CSRF-TOKEN': token 
                                        },
                                    });

            const   res =   await response.json();
        
            return res;

        } catch (error) {
            return {success:false,message:error,type:'view'};
        }

    }
</script>