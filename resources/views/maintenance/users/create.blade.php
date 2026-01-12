@extends('layouts.template')
@section('title')
    CREAR USUARIO
@endsection

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
            <h6>Datos del Usuario<i class="fa-solid fa-user"></i></h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    @include('maintenance.users.forms.form_create_usuario')
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <span style="color:rgb(219, 155, 35);font-size:14px;font-weight:bold;">Los campos con * son obligatorios</span>

            <div style="display:flex;">
                <button class="btn btn-danger btnVolver" style="margin-right:5px;" type="button">
                    <i class="fa-solid fa-door-open"></i> VOLVER
                </button>
                <button class="btn btn-primary" type="submit" form="formRegistrarUsuario">
                    <i class="fa-solid fa-floppy-disk"></i> REGISTRAR
                </button>
            </div>
        </div>
    </div>
@endsection

@section('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            iniciarSelect2();
            events();
        })

        function events() {
            document.querySelector('#formRegistrarUsuario').addEventListener('submit', (e) => {
                e.preventDefault();
                registrarUsuario();
            })

            document.addEventListener('click', (e) => {

                if (e.target.closest('.btnVolver')) {
                    const rutaIndex = '{{ route('tenant.mantenimientos.usuario.index') }}';
                    window.location.href = rutaIndex;
                }

                //======== BTN VER CONTRASEÑA =========
                if (e.target.closest('.btn_ver_password')) {

                    const btnVerPassword = e.target.closest('.btn_ver_password');
                    btnVerPassword.classList.toggle('password_oculto');

                    if (btnVerPassword.classList.contains('password_oculto')) {
                        //======== OCULTAR PASSWORD =======
                        document.querySelector('#password').type = 'password';
                        //===== CAMBIANDO ICONO =====
                        const icon = btnVerPassword.children[0];

                        icon.classList.add('hide-transition');
                        setTimeout(() => {
                            icon.className = 'fa-solid fa-eye-slash show-transition';
                            icon.classList.remove('hide-transition');
                        }, 300);
                    } else {
                        //====== MOSTRAR PASSWORD =======
                        document.querySelector('#password').type = 'text';
                        //===== CAMBIANDO ICONO =====
                        const icon = btnVerPassword.children[0];
                        icon.classList.add('hide-transition');
                        setTimeout(() => {
                            icon.className = 'fa-solid fa-eye show-transition';
                        }, 300);
                    }
                }

                if (e.target.closest('.btn_ver_repetir_password')) {

                    const btnVerRepetirPassword = e.target.closest('.btn_ver_repetir_password');
                    btnVerRepetirPassword.classList.toggle('password_oculto');

                    if (btnVerRepetirPassword.classList.contains('password_oculto')) {
                        //======== OCULTAR PASSWORD =======
                        document.querySelector('#repetir_password').type = 'password';
                        //===== CAMBIANDO ICONO =====
                        const icon = btnVerRepetirPassword.children[0];

                        icon.classList.add('hide-transition');
                        setTimeout(() => {
                            icon.className = 'fa-solid fa-eye-slash show-transition';
                            icon.classList.remove('hide-transition');
                        }, 300);

                    } else {
                        //====== MOSTRAR PASSWORD =======
                        document.querySelector('#repetir_password').type = 'text';
                        //===== CAMBIANDO ICONO =====
                        const icon = btnVerRepetirPassword.children[0];
                        icon.classList.add('hide-transition');
                        setTimeout(() => {
                            icon.className = 'fa-solid fa-eye show-transition';
                        }, 300);
                    }
                }

            })
        }

        function iniciarSelect2() {
            $('.select_2_form').select2({
                theme: "bootstrap-5",
                width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
                placeholder: $(this).data('placeholder'),
            });
        }

        function registrarUsuario() {
            Swal.fire({
                title: "DESEA REGISTRAR EL USUARIO?",
                text: "Se creará un nuevo usuario!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "SÍ, REGISTRAR!",
                cancelButtonText: "NO, CANCELAR!",
                reverseButtons: true
            }).then(async (result) => {
                if (result.isConfirmed) {

                    clearValidationErrors('msgError');
                    const token = document.querySelector('input[name="_token"]').value;
                    const formRegistrarUsuario = document.querySelector('#formRegistrarUsuario');
                    const formData = new FormData(formRegistrarUsuario);
                    const urlRegistrarUsuario = @json(route('tenant.mantenimientos.usuario.store'));

                    Swal.fire({
                        title: 'Cargando...',
                        html: 'Registrando nuevo usuario...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    try {
                        const response = await fetch(urlRegistrarUsuario, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': token
                            },
                            body: formData
                        });

                        const res = await response.json();

                        if (response.status === 422) {
                            if ('errors' in res) {
                                paintValidationErrors(res.errors, 'error');
                            }
                            Swal.close();
                            return;
                        }


                        if (res.success) {
                            const usuario_index = @json(route('tenant.mantenimientos.usuario.index'));
                            toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                            window.location.href = usuario_index;
                        } else {
                            toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                            Swal.close();
                        }


                    } catch (error) {
                        toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR USUARIO');
                        Swal.close();
                    }


                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    Swal.fire({
                        title: "OPERACIÓN CANCELADA",
                        text: "NO SE REALIZARON ACCIONES",
                        icon: "error"
                    });
                }
            });
        }
    </script>
@endsection
