<div class="modal fade" id="mdlNumeration" tabindex="-1" aria-labelledby="mdlNumerationLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="mdlNumerationLabel">Registrar Numeración</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

            @include('company.forms.form_edit_numeration_tenant')

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">CERRAR</button>
          <button type="submit" class="btn btn-primary" form="formBillingNumeration">GUARDAR</button>
        </div>
      </div>
    </div>
</div>


<script>
    let dtNumerations   =   null;

    function eventsMdlNumeration(){

        document.querySelector('#formBillingNumeration').addEventListener('submit',(e)=>{
            e.preventDefault();
            storeBillingNumeration(e.target);
        })

        $('#mdlNumeration').on('hidden.bs.modal', function () {
            clearMdlNumeration();
        });

    }

    function startDataTableNumeration(){
        const urlGetNumerations = '{{ route('tenant.mantenimientos.empresas.getListNumeration') }}';

        dtNumerations  =   new DataTable('#tbl_numeration',{
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetNumerations,
                type: 'GET'
            },
            columns: [
                { data: 'id', name: 'id' },
                { data: 'description', name: 'description' },
                { data: 'serie', name: 'serie' },
                { data: 'start_number', name: 'start_number' },
                { data: 'initiated', name: 'initiated' },
                {
                    data: null,
                    render: function(data, type, row) {



                        return `
                          <i class="fas fa-trash-alt btn btn-danger"></i>
                        `;
                    },
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
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

    function clearMdlNumeration(){
        $('#billing_type_document').val(null).trigger('change');

        document.querySelector('#serie').value          =   '';
        document.querySelector('#start_number').value   =   1;
    }

    function openMdlNumeration(){
        $('#mdlNumeration').modal('show');
    }

    function storeBillingNumeration(formNumeration){

        const billing_type_document_text = $('#billing_type_document').select2('data')[0].text;


        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: `DESEA REGISTRAR LA NUMERACIÓN DE ${billing_type_document_text}`,
        text: "Esto producirá cambios en la facturación!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, ACTUALIZAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Cargando...',
                html: 'Actualizando numeración...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {

                const token                     =   document.querySelector('input[name="_token"]').value;

                const formData                  =   new FormData(formNumeration);
                const urlStoreNumeration        =   @json(route('tenant.mantenimientos.empresas.storeNumeration'));

                const response  =   await fetch(urlStoreNumeration, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();

                if(response.status === 422){
                    if('errors' in res){
                        //pintarErroresValidacion(res.errors);
                    }
                    Swal.close();
                    return;
                }

                if(res.success){
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    dtNumerations.ajax.reload(null, false);

                    //========= ACTUALIZAR SELECT2 ======
                    const billing_type_document_id = $('#billing_type_document').val();
                    $('#billing_type_document option[value="' + billing_type_document_id + '"]').remove().trigger('change');

                    $('#mdlNumeration').modal('hide');
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN REGISTRAR VENTA');
                Swal.close();
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

    function setBillingType(billing_id){

        const lstBillingTypes   =   @json($billing_documents);

        const indexBilling  =   lstBillingTypes.findIndex((b)=>{
            return b.id == billing_id;
        })

        if(indexBilling === -1){
            return;
        }

        //=========== SE MANEJA UNA SOLA SERIE POR DOCUMENTO =======
        const prefix_serie  =   lstBillingTypes[indexBilling].parameter;
        document.querySelector('#serie').value  =   prefix_serie + '001';

    }

</script>
