<!DOCTYPE html>
<html lang="en">

<head>

    <base href="{{ asset('') }}">

    {{-- GXON Meta --}}
    <meta charset="utf-8">
    <meta name="theme-color" content="#316AFF">
    <meta name="robots" content="index, follow">
    <meta name="author" content="LayoutDrop">
    <meta name="format-detection" content="telephone=no">
    <meta name="description" content="GXON HR Login">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login | ErpTaller</title>

    {{-- Favicon --}}
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200..800&display=swap" rel="stylesheet">

    {{-- GXON Required CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/libs/flaticon/css/all/all.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/lucide/lucide.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/node-waves/waves.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}">

    {{-- GXON Main CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">

</head>

<body>

    <div class="page-layout">

        <div class="auth-wrapper min-vh-100 px-2"
            style="background-image: url({{ asset('assets/images/auth/auth.webp') }});
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;">

            <div class="row g-0 min-vh-100">
                <div class="col-xl-5 col-lg-6 px-sm-4 align-self-center ms-auto py-4">

                    <div class="card card-body p-sm-5 maxw-450px rounded-4 auth-card m-auto p-4" data-simplebar>

                        {{-- LOGO --}}
                        <div class="mb-4 text-center">
                            <a href="{{ url('/') }}">
                                <img class="visible-light" src="{{ asset('assets/images/logo-full.svg') }}">
                                <img class="visible-dark" src="{{ asset('assets/images/logo-full-white.svg') }}">
                            </a>
                        </div>

                        {{-- TITLE --}}
                        <div class="mb-4 text-center">
                            <h5 class="mb-1">Bienvenido a ErpTaller</h5>
                            <p>Inicia Sesión para acceder al panel</p>
                        </div>

                        {{-- STATUS --}}
                        @if (session('status'))
                            <div class="alert alert-success small">
                                {{ session('status') }}
                            </div>
                        @endif

                        {{-- ERRORS --}}
                        @if ($errors->any())
                            <div class="alert alert-danger small">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {{-- FORM --}}
                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-4">
                                <label class="form-label">Correo Electrónico</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email') }}"
                                    required autofocus>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>

                            <div class="d-flex justify-content-between mb-4">
                                <div class="form-check mb-0">
                                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe">
                                    <label class="form-check-label" for="rememberMe">
                                        Recordar
                                    </label>
                                </div>

                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}">
                                        Olvidaste tu contraseña?
                                    </a>
                                @endif
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary waves-effect waves-light w-100">
                                    Iniciar Sesión
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- GXON JS --}}
    <script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
    <script src="{{ asset('assets/js/appSettings.js') }}"></script>
    <script src="{{ asset('assets/js/main.js') }}"></script>

</body>

</html>
