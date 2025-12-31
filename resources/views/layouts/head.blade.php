<!-- begin::GXON Meta Basic -->
<meta charset="utf-8">
<meta name="theme-color" content="#316AFF">
<meta name="robots" content="index, follow">
<meta name="author" content="LayoutDrop">
<meta name="format-detection" content="telephone=no">
<meta name="keywords"
    content="HR Management, HR Dashboard, Admin Template, Admin Dashboard, Bootstrap Admin, HR Admin Panel, Employee Management, Human Resources Dashboard, Responsive Admin Template, Web App Dashboard, HRMS Admin, Staff Management Dashboard, Bootstrap 5 Admin, Modern Admin Template, Admin UI Kit, ThemeForest Admin Template, SaaS Dashboard, Project Management Admin, HR Web Application, RTL Dashboard">
<meta name="description"
    content="GXON is a professional and modern HR Management Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
<!-- end::GXON Meta Basic -->

<!-- begin::GXON Meta Social -->
<meta property="og:url" content="https://gxon.layoutdrop.com/demo/">
<meta property="og:site_name" content="GXON HR Management Admin Dashboard Template + RTL">
<meta property="og:type" content="website">
<meta property="og:locale" content="en_US">
<meta property="og:title" content="GXON HR Management Admin Dashboard Template + RTL">
<meta property="og:description"
    content="GXON is a professional and modern HR Management Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
<meta property="og:image" content="https://gxon.layoutdrop.com/demo/preview.png">
<!-- end::GXON Meta Social -->

<!-- begin::GXON Meta Twitter -->
<meta name="twitter:card" content="summary">
<meta name="twitter:url" content="https://gxon.layoutdrop.com/demo/">
<meta name="twitter:creator" content="@layoutdrop">
<meta name="twitter:title" content="GXON HR Management Admin Dashboard Template + RTL">
<meta name="twitter:description"
    content="GXON is a professional and modern HR Management Admin Dashboard Template built with Bootstrap. It includes light and dark modes, and is ideal for managing employees, attendance, payroll, recruitment, and more — perfect for HR software and admin panels.">
<!-- end::GXON Meta Twitter -->

<!-- begin::GXON Website Page Title -->
{{-- <title>GXON HR Management Admin Dashboard Template + RTL</title> --}}
<title>ErpTaller</title>

<!-- end::GXON Website Page Title -->

<!-- begin::GXON Mobile Specific -->
<meta name="viewport" content="width=device-width, initial-scale=1">
<!-- end::GXON Mobile Specific -->

<!-- begin::GXON Favicon Tags -->
<link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/images/apple-touch-icon.png') }}">
<!-- end::GXON Favicon Tags -->

<!-- begin::GXON Google Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&display=swap"
    rel="stylesheet">
<!-- end::GXON Google Fonts -->

<!-- begin::GXON Required Stylesheet -->
<link rel="stylesheet" href="{{ asset('assets/libs/flaticon/css/all/all.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/lucide/lucide.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/fontawesome/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/simplebar/simplebar.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/node-waves/waves.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/bootstrap-select/css/bootstrap-select.min.css') }}">
<!-- end::GXON Required Stylesheet -->

<!-- begin::GXON CSS Stylesheet -->
<link rel="stylesheet" href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/libs/datatables/datatables.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
<link rel="stylesheet" href="{{ asset('assets/css/style-own.css') }}">

<!-- end::GXON CSS Stylesheet -->

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" />
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />


<style>
.loader-app {
  height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
}

.car__body {
  -webkit-animation: shake 1s ease-in-out infinite alternate; /* antes 0.2s */
          animation: shake 1s ease-in-out infinite alternate;
}

.car__line {
  transform-origin: center right;
  stroke-dasharray: 22;
  -webkit-animation: line 2s ease-in-out infinite; /* antes 0.8s */
          animation: line 2s ease-in-out infinite;
  -webkit-animation-fill-mode: both;
          animation-fill-mode: both;
}

.car__line--top {
  -webkit-animation-delay: 0s;
          animation-delay: 0s;
}
.car__line--middle {
  -webkit-animation-delay: 0.5s; /* antes 0.2s */
          animation-delay: 0.5s;
}
.car__line--bottom {
  -webkit-animation-delay: 1s; /* antes 0.4s */
          animation-delay: 1s;
}

@-webkit-keyframes shake {
  0% { transform: translateY(-1%); }
  100% { transform: translateY(3%); }
}

@keyframes shake {
  0% { transform: translateY(-1%); }
  100% { transform: translateY(3%); }
}

@-webkit-keyframes line {
  0% { stroke-dashoffset: 22; }
  25% { stroke-dashoffset: 22; }
  50% { stroke-dashoffset: 0; }
  51% { stroke-dashoffset: 0; }
  80% { stroke-dashoffset: -22; }
  100% { stroke-dashoffset: -22; }
}

@keyframes line {
  0% { stroke-dashoffset: 22; }
  25% { stroke-dashoffset: 22; }
  50% { stroke-dashoffset: 0; }
  51% { stroke-dashoffset: 0; }
  80% { stroke-dashoffset: -22; }
  100% { stroke-dashoffset: -22; }
}


/* Overlay real */
.loader-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(255, 255, 255, 1);
    display: flex;
    justify-content: center;
    align-items: center;
    opacity: 0;
    visibility: hidden;
    transition: opacity 0.3s ease, visibility 0.3s ease;
    z-index: 9999;
}

.loader-overlay.active {
    opacity: 1;
    visibility: visible;
}


.menu-link svg {
    width: 32px;
    height: 32px;
}


</style>

@yield('css')
@stack('styles')
