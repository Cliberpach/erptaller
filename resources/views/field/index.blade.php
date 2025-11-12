@extends('layouts.template')

@section('title')
    Egresos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between flex-row">
            <h4 class="card-title">LISTA DE CAMPOS</h4>
            @if ($create == true)
                <div class="input-group-append">
                    <a href="{{ route('tenant.campos.create') }}" class="btn btn-primary">
                        <div class="lign-items-center d-flex align-items-center">
                            <i class="fas fa-plus pe-1"></i>
                            <p class="mb-0 ml-2">Añadir nuevo</p>
                        </div>
                    </a>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col">
                <table id="miTabla" style="width:100%" class="table-hover table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tipo de campo</th>
                            <th>Campo</th>
                            <th>Precio Día</th>
                            <th>Precio Noche</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($fields->count() > 0)
                            @foreach ($fields as $field)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $field->typeField->description }}</td>
                                    <td>{{ $field->field }}</td>
                                    <td>S/ {{ number_format($field->day_price, 2) }}</td>
                                    <td>S/ {{ number_format($field->night_price, 2) }}</td>
                                    <td>
                                        @if ($field->status == 'LIBRE')
                                            <span class="badge bg-success">Disponible</span>
                                        @elseif ($field->status == 'RESERVADO')
                                            <span class="badge bg-info">Reservado</span>
                                        @else
                                            <span class="badge bg-warning">Alquilado</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex">
                                            <a class="btn btn-primary btn-sm me-1"
                                                href="{{ route('tenant.campos.edit', $field->id) }}">
                                                Editar
                                            </a>

                                            <form action="{{ route('tenant.campos.delete', $field->id) }}" method="POST"
                                                onsubmit="return confirm('¿Estás seguro de que quieres eliminar este campo?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">Eliminar</button>
                                            </form> 
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">NO DATA</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if (!$create)
        <p class="text-danger">No puedes crear más campos según tu plan actual,debes subir de plan.</p>
    @endif

@endsection


@section('js')
    <script>
        document.addEventListener('DOMContentLoaded',()=>{
            showAlerts();
        })

        function showAlerts(){
            toastr.clear();
            const hasMessageError    =   @json(Session::has('field_error'));
            console.log(hasMessageError)

            if(hasMessageError){
                const message       =   @json(Session::get('field_error'));
                toastr.error(message,'OPERACIÓN INCORRECTA');
            }
        }
    </script>
@endsection
