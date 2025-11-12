@extends('layouts.template')

@section('title')
    Empresa
@endsection

@section('css')
@endsection

@section('content')
    <div class="card">
        @csrf
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">MANTENIMIENTO DE EMPRESAS</h5>
            <span class="float-end">
                <a href="{{ route('landlord.mantenimientos.empresas.create') }}" class="btn btn-outline-primary me-1">Nueva
                    Empresa</a>
            </span>
        </div>
        <div class="table-responsive text-nowrap">

            @include('company.tables.tbl_landlord_companies')
           
        </div>
    </div>
@endsection


@section('js')
<script>
    let dtCompaniesLandlord =   null;

    document.addEventListener('DOMContentLoaded',()=>{
        events();
        startDataTableCompanies();
    })

    function events(){

    }

    function startDataTableCompanies() {
        const urlGetCompanies = '{{ route('landlord.mantenimientos.getCompanies') }}';

        dtCompaniesLandlord = new DataTable('#tbl_landlord_companies', {
            serverSide: true,
            processing: true,
            ajax: {
                url: urlGetCompanies,
                type: 'GET'
            },
            order: [[0, 'desc']],
            columns: [
                {
                    data: 'domain',
                    render: function (data, type, row) {
                        return `<a href="https://${data}/login" target="_blank">${data}</a>`;
                    },
                    name: 'domain',
                    orderable: false,
                    searchable: false
                },
                { data: 'business_name', name: 'business_name' },
                { data: 'ruc', name: 'ruc' },
                { data: 'plan_name', name: 'plan_name' },
                { data: 'email', name: 'email' },
                {
                    data: 'invoicing_status',
                    render: function (data) {
                        return data === 1 ? 'SI' : 'NO';
                    },
                    name: 'invoicing_status'
                },
                { data: 'created_at', name: 'created_at' },
                {
                    data: null,
                    render: function (data) {
                      
                        const urlEditCompany = "{{ route('landlord.mantenimientos.empresas.edit', ':id') }}".replace(':id', data.id);

                        return `<div class="btn-group">
                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-bars"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="resetPassword(${data.id});"> 
                                        <i class="fas fa-key"></i> Resetear clave
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="${urlEditCompany}">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="deleteTenant(${data.id});">
                                        <i class="fas fa-trash-alt"></i> Eliminar
                                    </a>
                                </li>
                            </ul>
                            </div>`;
                    },
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }  
            ],
            pageLength: 25,
            lengthChange: false,
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron resultados",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                },
                loadingRecords: "Cargando...",
                processing: "Procesando...",
                emptyTable: "No hay datos disponibles en la tabla",
                aria: {
                    sortAscending: ": activar para ordenar la columna de manera ascendente",
                    sortDescending: ": activar para ordenar la columna de manera descendente"
                }
            }
        });
    }

    function resetPassword(company_id){

        const company =   getRowById(dtCompaniesLandlord,company_id);

        let message =   `Resetear la clave de: ${company.business_name}-${company.ruc}?`;

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
        confirmButtonText: "Sí, resetear!",
        cancelButtonText: "No, cancelar!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {
        
            Swal.fire({
                title: `Reseteando clave...`,
                html: "Porfavor espere...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {

                toastr.clear();
                const token                   =   document.querySelector('input[name="_token"]').value;
                
                const formData                =   new FormData();
                const urlResetPassword        =   @json(route('landlord.mantenimientos.empresas.resetearClave'));

                formData.append('company_id',company_id);

                const response  =   await fetch(urlResetPassword, {
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
                    dtCompaniesLandlord.ajax.reload(null, false);
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    //window.location.href    =   sale_index;
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN RESETEAR CLAVE');
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


    function deleteTenant(company_id){
        const company =   getRowById(dtCompaniesLandlord,company_id);

        let message =   `Eliminar empresa: ${company.business_name}-${company.ruc}?`;

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
        confirmButtonText: "Sí, eliminar!",
        cancelButtonText: "No, cancelar!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: `Eliminando empresa...`,
                html: "Porfavor espere...",
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });

            try {

                toastr.clear();
                const token                     =   document.querySelector('input[name="_token"]').value;
                
                const formData                  =   new FormData();
                const urlDeleteTenant           =   "{{ route('landlord.mantenimientos.empresas.deleteTenant', ':id') }}".replace(':id', company_id);

                formData.append('company_id',company_id);

                const response  =   await fetch(urlDeleteTenant, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token,
                                            'X-HTTP-Method-Override': 'DELETE' 
                                        }
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
                    dtCompaniesLandlord.ajax.reload(null, false);
                    toastr.success(res.message,'OPERACIÓN COMPLETADA');
                    //window.location.href    =   sale_index;
                    Swal.close();
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                    Swal.close();
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ELIMINAR EMPRESA');
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
