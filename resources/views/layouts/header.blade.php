<meta charset="utf-8">
<meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0">
<meta name="description" content="">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>TallerSuite | @yield('title') </title>
<link rel="icon" type="image/png" href="{{ asset('loginn/img/icono.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="{{ asset('assets/vendor/fonts/boxicons.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}?v=1" class="template-customizer-core-css">
<link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}" class="template-customizer-theme-css">
<link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
{{-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/toastr.min.css') }}"> --}}
<link rel="stylesheet"
    href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap">
{{-- <script src="https://kit.fontawesome.com/f9bb7aa434.js" crossorigin="anonymous"></script> --}}

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- <link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/cr-1.7.0/rr-1.4.1/sp-2.2.0/datatables.min.css" rel="stylesheet"> --}}
{{-- <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css"> --}}

<!-- ======== SELECT2 ========== -->
<!-- Styles -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />

<!-- ======== DATATABLE ====== -->
<link href="{{ asset('assets/datatable/datatables.min.css') }}" rel="stylesheet">

<!-- ========== FONTAWESOME ============ -->
<link href="{{ asset('assets/fontawesome/css/fontawesome.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/fontawesome/css/brands.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/fontawesome/css/solid.css') }}" rel="stylesheet" />
<link href="{{ asset('assets/css/utils.css') }}" rel="stylesheet" />

@yield('css')
@stack('styles')

