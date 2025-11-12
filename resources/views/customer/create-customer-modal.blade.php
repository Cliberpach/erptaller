@extends('layouts.template')

@section('title')
    Registrar nuevo cliente
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title">Registrar nuevo cliente</h5>
                    </div>
                    <div class="card-body">
                        <form id="createCustomerForm" action="{{ route('tenant.ventas.cliente.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="document_number" class="form-label">DNI</label>
                                <div class="input-group">
                                    <input type="text" id="document_number" name="document_number" class="form-control @error('document_number') is-invalid @enderror" value="{{ old('document_number') }}" placeholder="Ingrese DNI">
                                    <button class="btn btn-outline-primary" type="button" id="btn_consulta_sunat" style="padding-right: 10px; padding-left: 10px;">
                                        <i class="bx bx-search"></i>
                                    </button>
                                    @error('document_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                </div>
                                
                            </div>
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre (*)</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Ingrese nombre" value="{{ old('name') }}">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Celular (*)</label>
                                <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror" placeholder="Ingrese número de celular" value="{{ old('phone') }}">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('click', function(event) {
            if (event.target && event.target.id === 'btn_consulta_sunat') {
                const userDocument = document.getElementById('document_number').value;

                if (userDocument.length === 8) {
                    Swal.fire({
                        title: 'Consultar',
                        text: "¿Desea consultar DNI a Sunat?",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: "#696cff",
                        confirmButtonText: 'Si, Confirmar',
                        cancelButtonText: "No, Cancelar",
                        showLoaderOnConfirm: true,
                        preConfirm: function() {
                            var url = '/landlord/dni/' + userDocument;
                            return fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                }
                            }).then(response => response.json())
                              .catch(error => {
                                  console.error('Error al consultar la API:', error);
                                  Swal.fire({
                                      icon: 'error',
                                      title: 'Error',
                                      text: 'Hubo un problema al consultar la API.'
                                  });
                              });
                        },
                        allowOutsideClick: function() {
                            return !Swal.isLoading();
                        }
                    }).then(function(result) {
                        if (result.isConfirmed) {
                            var data = result.value;
                            if (data.success === false) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'DNI inválido o no existe!'
                                });
                            } else {
                                document.getElementById('name').value = data.data.nombre_completo;
                            }
                        }
                    }).catch(function(error) {
                        console.error('Error al consultar la API:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al consultar la API.'
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Mala escritura de DNI'
                    });
                }
            }
        });
    </script>
@endsection

