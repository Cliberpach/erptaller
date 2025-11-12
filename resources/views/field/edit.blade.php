@extends('layouts.template')

@section('title')
    Egresos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    @if (session('datos'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            {{ session('datos') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card">
        <form action="{{ route('tenant.campos.update', $field->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-header d-flex justify-content-between flex-row">
                <h4 class="card-title">EDITAR CAMPO</h4>

                <div class="input-group-append">
                    <a href="{{ route('tenant.campos.campo') }}" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                </div>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 col-12">
                        <div class="form-group mb-3">
                            <div class="d-flex align-items-center">
                                <label for="type_field_id">Tipo de campo </label>
                                <button class="btn btn-rounded p-0" type="button" onclick="openCreateTypeFieldModal()">
                                    [<i class='bx bx-plus'></i> Nuevo]
                                </button>
                            </div>
                            <select name="type_field_id" id="type_field_id" class="form-control">
                                @foreach ($type_fields as $type_field)
                                    <option value="{{ $type_field->id }}"
                                        {{ $type_field->id == $field->type_field_id ? 'selected' : '' }}>
                                        {{ $type_field->description }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8 col-12">
                        <div class="form group mb-3">
                            <label for="field">Campo</label>
                            <input type="text" name="field" id="field" class="form-control" oninput="this.value = this.value.toUpperCase()" required
                                value="{{ $field->field }}">
                        </div>
                    </div>
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label for="day_price">Precio Día</label>
                        <input type="number" name="day_price" id="day_price" class="form-control" required value="{{$field->day_price}}">
                    </div>
    
                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                        <label for="night_price">Precio Noche</label>
                        <input type="number" name="night_price" id="night_price" class="form-control" required value="{{$field->night_price}}">
                    </div>
                </div>

                <div class="form group">
                    <label for="date">Ubicación</label>
                    <textarea name="location" id="location" cols="3" rows="3" class="form-control" oninput="this.value = this.value.toUpperCase()">{{ $field->location }}</textarea>
                </div>
            </div>
        </form>
    </div>

    @include('field.create-type-field-modal')
@endsection


@section('js')
    <script>
        function openCreateTypeFieldModal() {
            $('#createTypeFieldModal').modal('toggle');
        }
    </script>
@endsection
