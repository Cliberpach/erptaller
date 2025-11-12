@extends('layouts.template')

@section('title', 'Egresos')

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0 text-white">Tipos de Campos</h5>
                <a onclick="openCreateTypeFieldModal()" class="btn btn-success btn-sm">
                    <i class="fas fa-plus-circle"></i> Crear nuevo
                </a>
            </div>
            <div class="card-body p-0">
                <table id="miTabla" class="table table-hover table-striped mb-0">
                    <thead class="thead-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Tipo de campo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="text-center">
                        @foreach ($type_field as $field)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $field->description }}</td>
                                <td>
                                    <div class="d-flex justify-content-center">
                                        <button class="btn btn-primary btn-sm edit-button" data-id="{{ $field->id }}" data-description="{{ $field->description }}">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                        <form action="{{ route('tenant.campos.delete_tipo_campos', ['id' => $field->id]) }}" method="POST" 
                                            onsubmit="return confirm('¿Estás seguro de que quieres eliminar este campo?')">
                                          @csrf
                                          @method('DELETE')
                                          <button type="submit" class="btn btn-danger btn-sm">
                                              <i class="fas fa-trash-alt"></i> Eliminar
                                          </button>
                                      </form>
                                    </div>                                    
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@include('field.create-type-field-modal')
@include('field.edit-type-field-modal')
@endsection

@section('js')
<script>
    function openCreateTypeFieldModal() {
        $('#createTypeFieldModal').modal('toggle');
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Escuchar clic en los botones de editar
        document.querySelectorAll('.edit-button').forEach(button => {
            button.addEventListener('click', function () {
                const fieldId = this.dataset.id;
                const description = this.dataset.description;
                const form = document.getElementById('editTypeFieldForm');

                // Establecer la acción del formulario con la URL correcta para editar
                form.action = `/campos/tipo-campos/${fieldId}`;

                // Establecer el valor actual del campo de descripción
                form.querySelector('#description').value = description;

                // Abrir el modal de edición
                new bootstrap.Modal(document.getElementById('editTypeFieldModal')).show();
            });
        });
    });
</script>
@endsection
