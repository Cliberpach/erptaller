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
                    <div class="card-body">
                        @forelse ($porGrupo->get($key, collect()) as $item)
                            <div class="mb-3">
                                {{-- label como bloque (d-block) para que el texto largo envuelva y no desborde la card angosta --}}
                                <label for="configuration_{{ $item->id }}" class="d-block fw-bold mb-2" style="word-break: break-word;">
                                    {{ $item->description }}
                                </label>
                                <x-toggle-switch-1 id="configuration_{{ $item->id }}" name="configuration_{{ $item->id }}"
                                    :checked="$item->property == 1" />
                                <p class="configuration_{{ $item->id }}_error msgError"></p>
                            </div>
                        @empty
                            <p class="text-muted mb-0">
                                <i class="fas fa-info-circle me-1"></i>Sin configuraciones aún
                            </p>
                        @endforelse
                    </div>
                </div>
            </div>
        @endforeach

        {{-- 6ta card: ZONA DE PELIGRO, integrada en la grilla (esquina inferior derecha). Vacía por
             ahora -> las acciones destructivas se definen en una tarea posterior. Sin inputs. --}}
        <div class="col-12 col-md-4">
            <div class="card h-100 border-danger shadow-sm">
                <div class="card-header bg-danger text-white d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <h6 class="card-title mb-0">Zona de Peligro</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">Sin acciones disponibles aún.</p>
                </div>
            </div>
        </div>
    </div>
</form>
