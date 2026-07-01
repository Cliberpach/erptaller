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

    @foreach ($grupos as $key => $label)
        <div class="card mb-3 shadow-sm">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $label }}</h5>
            </div>
            <div class="card-body">
                @forelse ($porGrupo->get($key, collect()) as $item)
                    <div class="row mb-4">
                        <div class="col-lg-6 col-md-6 col-sm-6 d-flex align-items-center">
                            <label for="configuration_{{ $item->id }}" style="font-weight: bold;">
                                {{ $item->description }}
                            </label>
                        </div>

                        <div class="col-lg-6 col-md-6 col-sm-6">
                            <x-toggle-switch-1 id="configuration_{{ $item->id }}" name="configuration_{{ $item->id }}"
                                :checked="$item->property == 1" />

                            <p class="configuration_{{ $item->id }}_error msgError"></p>
                        </div>
                    </div>
                @empty
                    <p class="text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>Sin configuraciones aún
                    </p>
                @endforelse
            </div>
        </div>
    @endforeach
</form>
