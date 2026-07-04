@extends('layouts.template')

@section('title')
    Copias de Seguridad
@endsection

@section('css')
@endsection

@section('content')
    <div class="card">
        @csrf
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">COPIAS DE SEGURIDAD</h5>
                <small class="text-muted">Respaldo global: BD central + todas las empresas + archivos, en un solo
                    .tar.gz</small>
            </div>
            <button type="button" id="btnGenerarBackup" class="btn btn-primary" onclick="generarBackup();">
                <i class="fas fa-database"></i> Generar backup ahora
            </button>
        </div>
        <div class="card-body">
            <div id="estadoBackupBanner" class="alert d-none" role="alert"></div>

            <div class="table-responsive text-nowrap">
                <table class="table" id="tbl_backups">
                    <thead>
                        <tr>
                            <th>ARCHIVO</th>
                            <th>FECHA</th>
                            <th>TAMAÃ‘O</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="tbodyBackups">
                    </tbody>
                </table>
            </div>

            <small class="text-muted">
                Se conservan los Ãºltimos 3 backups. Respaldo automÃ¡tico diario a las 3:00 AM. Almacenamiento solo en
                el servidor.
            </small>
        </div>
    </div>
@endsection

@section('js')
<script>
    let pollBackups   =   null;

    document.addEventListener('DOMContentLoaded', () => {
        cargarEstado();
        pollBackups = setInterval(cargarEstado, 5000);
    });

    async function cargarEstado() {
        try {
            const response  =   await fetch('{{ route('landlord.configuracion.backups.estado') }}');
            const data      =   await response.json();

            pintarBanner(data.estado);
            pintarTabla(data.backups);
        } catch (error) {
            console.error(error);
        }
    }

    function pintarBanner(estado) {
        const banner        =   document.getElementById('estadoBackupBanner');
        const btnGenerar     =   document.getElementById('btnGenerarBackup');

        banner.classList.remove('d-none', 'alert-info', 'alert-success', 'alert-danger');

        if (estado.estado === 'en_proceso') {
            banner.classList.add('alert-info');
            banner.innerHTML   =   '<i class="fas fa-spinner fa-spin"></i> Generando backup...';
            btnGenerar.disabled =   true;
        } else if (estado.estado === 'completado') {
            banner.classList.add('alert-success');
            banner.innerHTML   =   `<i class="fas fa-check-circle"></i> ${estado.mensaje} (${estado.fecha})`;
            btnGenerar.disabled =   false;
        } else if (estado.estado === 'error') {
            banner.classList.add('alert-danger');
            banner.innerHTML   =   `<i class="fas fa-exclamation-triangle"></i> ${estado.mensaje}`;
            btnGenerar.disabled =   false;
        } else {
            banner.classList.add('d-none');
            btnGenerar.disabled =   false;
        }
    }

    function pintarTabla(backups) {
        const tbody =   document.getElementById('tbodyBackups');

        if (!backups.length) {
            tbody.innerHTML =   '<tr><td colspan="4" class="text-center">AÃºn no hay backups generados.</td></tr>';
            return;
        }

        tbody.innerHTML =   backups.map(b => {
            const urlDescargar  =   "{{ route('landlord.configuracion.backups.descargar', ':archivo') }}".replace(':archivo', b.nombre);

            return `<tr>
                <td>${b.nombre}</td>
                <td>${b.fecha}</td>
                <td>${b.tamano}</td>
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bars"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li>
                                <a class="dropdown-item" href="${urlDescargar}">
                                    <i class="fas fa-download"></i> Descargar
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="eliminarBackup('${b.nombre}');">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    function generarBackup() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            title: 'Â¿Generar backup ahora?',
            text: 'Se encolarÃ¡ en segundo plano.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'SÃ­, generar!',
            cancelButtonText: 'No, cancelar!',
            reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) {
                return;
            }

            try {
                toastr.clear();
                const token         =   document.querySelector('input[name="_token"]').value;
                const urlGenerar    =   @json(route('landlord.configuracion.backups.generar'));

                const response  =   await fetch(urlGenerar, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token
                                        }
                                    });

                const res   =   await response.json();

                if (res.success) {
                    toastr.success(res.message, 'OPERACIÃ“N COMPLETADA');
                    cargarEstado();
                } else {
                    toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÃ“N GENERAR BACKUP');
            }
        });
    }

    function eliminarBackup(archivo) {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });

        swalWithBootstrapButtons.fire({
            title: `Eliminar backup: ${archivo}?`,
            text: 'OPERACIÃ“N NO REVERSIBLE!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'SÃ­, eliminar!',
            cancelButtonText: 'No, cancelar!',
            reverseButtons: true
        }).then(async (result) => {
            if (!result.isConfirmed) {
                return;
            }

            try {
                toastr.clear();
                const token         =   document.querySelector('input[name="_token"]').value;
                const urlEliminar   =   "{{ route('landlord.configuracion.backups.eliminar', ':archivo') }}".replace(':archivo', archivo);

                const response  =   await fetch(urlEliminar, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token,
                                            'X-HTTP-Method-Override': 'DELETE'
                                        }
                                    });

                const res   =   await response.json();

                if (res.success) {
                    toastr.success(res.message, 'OPERACIÃ“N COMPLETADA');
                    cargarEstado();
                } else {
                    toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                }
            } catch (error) {
                toastr.error(error, 'ERROR EN LA PETICIÃ“N ELIMINAR BACKUP');
            }
        });
    }
</script>
@endsection
