<form action="" id="frmConfiguration">
    @php
        // Orden FIJO de los grupos (independiente del orden en la tabla). El agrupamiento es solo
        // presentación: los toggles siguen siendo configuration_{id} -> el guardado no cambia.
        $grupos = [
            'VENTAS'  => 'Ventas',
            'COMPRAS' => 'Compras',
            'OT'      => 'Órdenes de Trabajo',
            'SUNAT'   => 'SUNAT',
            'EMPRESA' => 'Empresa',
        ];
        $porGrupo = $configuration->groupBy('group_name');
    @endphp

    {{-- Grilla 3x2: col-md-4 -> 3 cards por fila. Fila1: Ventas/Compras/OT. Fila2: SUNAT/Empresa/Peligro.
         En móvil (col-12) se apilan. h-100 -> cards de igual alto por fila. --}}
    <div class="row g-3">
        @foreach ($grupos as $key => $label)
            <div class="col-12 col-md-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">
                        <h6 class="card-title mb-0">{{ $label }}</h6>
                    </div>
                    <div class="card-body py-2">
                        @if ($key === 'SUNAT')
                            {{-- IGV vive en companies.igv (tenant), no en la tabla configuration -->
                                 no tiene configuration_{id}, se guarda aparte en el controller. --}}
                            <div class="config-opt border-bottom py-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="igv" class="config-opt-label mb-0 me-2">IGV (%)</label>
                                    <input type="number" id="igv" name="igv" min="0" max="100" step="0.01"
                                        class="form-control form-control-sm flex-shrink-0" style="width:90px;"
                                        value="{{ $company->igv ?? 18 }}">
                                </div>
                                <p class="igv_error msgError mb-0"></p>
                            </div>
                        @endif

                        @forelse ($porGrupo->get($key, collect()) as $item)
                            {{-- opción compacta: label a la izq (envuelve si es largo) + control chico a la der, en 1 fila.
                                 AMB_GRE es symbol tipo "select" (DEMO/PRODUCCION); UMB_DNI es numérico libre; el resto son toggles 0/1. --}}
                            <div class="config-opt border-bottom py-1">
                                <div class="d-flex align-items-center justify-content-between">
                                    <label for="configuration_{{ $item->id }}" class="config-opt-label mb-0 me-2">
                                        {{ $item->description }}
                                    </label>
                                    @if ($item->symbol === 'AMB_GRE')
                                        <select id="configuration_{{ $item->id }}" name="configuration_{{ $item->id }}"
                                            class="form-select form-select-sm flex-shrink-0" style="width:auto;">
                                            <option value="DEMO" @selected($item->property === 'DEMO')>BETA (pruebas)</option>
                                            <option value="PRODUCCION" @selected($item->property === 'PRODUCCION')>PRODUCCIÓN</option>
                                        </select>
                                    @elseif ($item->symbol === 'UMB_DNI')
                                        <input type="number" id="configuration_{{ $item->id }}" name="configuration_{{ $item->id }}"
                                            min="0" step="0.01" class="form-control form-control-sm flex-shrink-0"
                                            style="width:90px;" value="{{ $item->property }}">
                                    @else
                                        <span class="config-opt-toggle flex-shrink-0">
                                            <x-toggle-switch-1 id="configuration_{{ $item->id }}" name="configuration_{{ $item->id }}"
                                                :checked="$item->property == 1" />
                                        </span>
                                    @endif
                                </div>
                                <p class="configuration_{{ $item->id }}_error msgError mb-0"></p>
                            </div>
                        @empty
                            @if ($key !== 'SUNAT')
                                <p class="text-muted mb-0">
                                    <i class="fas fa-info-circle me-1"></i>Sin configuraciones aún
                                </p>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach

        {{-- 6ta card: HERRAMIENTAS DE ADMINISTRACIÓN (antes "Zona de Peligro" vacía). Solo admin
             (gate reforzado también en el controller, esto es solo UI). --}}
        <div class="col-12 col-md-4">
            <div class="card h-100 border-danger shadow-sm">
                <div class="card-header bg-danger text-white d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <h6 class="card-title mb-0">Herramientas de Administración</h6>
                </div>
                <div class="card-body d-flex flex-column gap-2">
                    <a href="{{ route('tenant.mantenimientos.configuracion.backup') }}"
                        class="btn btn-outline-success btn-sm">
                        <i class="fas fa-database me-1"></i> Descargar Backup (Base de Datos)
                    </a>
                    <button type="button" class="btn btn-outline-warning btn-sm"
                        onclick="abrirModalLimpiar('documentos')">
                        <i class="fas fa-eraser me-1"></i> Eliminar Documentos (conserva catálogo)
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm"
                        onclick="abrirModalLimpiar('todo')">
                        <i class="fas fa-radiation me-1"></i> Eliminar TODO (empezar de cero)
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Modal de confirmación reforzada para las acciones destructivas --}}
<div class="modal fade" id="modal_confirmar_limpiar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modal_confirmar_limpiar_titulo">Confirmar acción</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="modal_confirmar_limpiar_texto" class="text-danger fw-bold"></p>
                <label class="form-label">Escriba <b>ELIMINAR</b> para confirmar:</label>
                <input type="text" id="input_confirmar_limpiar" class="form-control" autocomplete="off">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="btn_confirmar_limpiar" disabled onclick="ejecutarLimpiar()">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>
</div>
