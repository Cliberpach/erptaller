@extends('layouts.template')

@section('title')
    ALMACENES
@endsection

@section('content')
    @include('inventory.warehouses.modals.modal_create_warehouse')
    @include('inventory.warehouses.modals.modal_edit_warehouse')

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h4 class="card-title mb-md-0 mb-2">ALMACENES <small class="text-muted">(todas mis sedes)</small></h4>
            <div class="d-flex flex-wrap gap-2">
                <a onclick="openMdlNuevoAlmacen()" class="btn btn-primary text-white">
                    <i class="fas fa-plus-circle"></i> Nuevo almacén
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                @include('inventory.warehouses.tables.table_list_warehouses')
            </div>
        </div>
    </div>
@endsection

<style>
    .swal2-container { z-index: 9999999; }
</style>

<script>
    let dtAlmacenes = null;

    document.addEventListener('DOMContentLoaded', () => {
        iniciarDataTableAlmacenes();
        eventsMdlCreateAlmacen();
        eventsMdlEditAlmacen();
    })

    function iniciarDataTableAlmacenes() {
        const url = '{{ route('tenant.inventarios.almacenes.getWarehouses') }}';
        dtAlmacenes = new DataTable('#table_almacenes', {
            serverSide: true,
            processing: true,
            ajax: { url: url, type: 'GET' },
            order: [[0, 'asc']],
            columns: [
                { data: 'descripcion', name: 'descripcion' },
                {
                    data: 'sede_nombre', name: 'sede_nombre', orderable: false, searchable: false,
                    render: d => `<span class="badge bg-info">${d ?? '-'}</span>`
                },
                {
                    data: 'es_principal', name: 'es_principal', orderable: false, searchable: false,
                    render: d => Number(d) === 1
                        ? '<span class="badge bg-primary">PRINCIPAL</span>'
                        : '<span class="badge bg-secondary">ADICIONAL</span>'
                },
                {
                    data: 'estado', name: 'estado', orderable: false, searchable: false,
                    render: d => d === 'ACTIVO'
                        ? '<span class="badge bg-success">ACTIVO</span>'
                        : '<span class="badge bg-warning text-dark">INACTIVO</span>'
                },
                {
                    data: null, name: 'actions', orderable: false, searchable: false,
                    render: function (data, type, row) {
                        const accion = row.estado === 'ACTIVO'
                            ? `<i class="fa-solid fa-ban"></i> Desactivar`
                            : `<i class="fa-solid fa-check"></i> Activar`;

                        let toggleItem = '';
                        if (Number(row.es_principal) !== 1) {
                            toggleItem = `
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item" href="javascript:void(0);" onclick="toggleEstadoAlmacen(${data.id})">
                                        ${accion}
                                    </a>
                                </li>`;
                        }

                        return `
                            <div class="btn-group dropstart">
                              <button type="button" class="dropdown-toggle btn btn-primary" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-grip"></i>
                              </button>
                              <ul class="dropdown-menu">
                                <li>
                                  <a class="dropdown-item" href="javascript:void(0);" onclick="openMdlEditAlmacen(${data.id})">
                                    <i class="fa-solid fa-pen-to-square"></i> Editar
                                  </a>
                                </li>
                                ${toggleItem}
                              </ul>
                            </div>`;
                    }
                }
            ],
            language: {
                "lengthMenu": "Mostrar _MENU_ registros por página",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "search": "Buscar:",
                "paginate": { "first": "Primero", "last": "Último", "next": "Siguiente", "previous": "Anterior" },
                "loadingRecords": "Cargando...", "processing": "Procesando...",
                "emptyTable": "No hay almacenes en la sede activa"
            }
        });
    }

    function toggleEstadoAlmacen(id) {
        toastr.clear();
        let row = getRowById(dtAlmacenes, id);
        const swalBtns = Swal.mixin({ customClass: { confirmButton: "btn btn-success", cancelButton: "btn btn-danger" }, buttonsStyling: false });
        const accion = row.estado === 'ACTIVO' ? 'DESACTIVAR' : 'ACTIVAR';

        swalBtns.fire({
            title: `¿DESEA ${accion} EL ALMACÉN?`,
            text: `Almacén: ${row.descripcion}`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: `Sí, ${accion.toLowerCase()}!`,
            cancelButtonText: "No, cancelar!",
            reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) return;
            Swal.fire({ title: 'Cargando...', html: 'Actualizando estado...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                let url = `{{ route('tenant.inventarios.almacenes.toggleStatus', ['id' => ':id']) }}`.replace(':id', id);
                const token = document.querySelector('input[name="_token"]').value;
                const response = await fetch(url, { method: 'PUT', headers: { 'X-CSRF-TOKEN': token } });
                const res = await response.json();
                if (res.success) {
                    dtAlmacenes.draw();
                    toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                } else {
                    toastr.warning(res.message, 'OPERACIÓN NO PERMITIDA');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÓN');
            } finally {
                Swal.close();
            }
        });
    }
</script>
