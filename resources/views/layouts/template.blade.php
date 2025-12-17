<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @routes
    @include('layouts.head')
</head>

<body>
    <div class="page-layout">

        <!-- begin::GXON Page Header -->
        <header class="app-header">
            @include('layouts.body.header')
        </header>
        <!-- end::GXON Page Header -->

        @include('layouts.body.mdl_search')

        <!-- begin::GXON Sidebar Menu -->
        <aside class="app-menubar" id="appMenubar">
            @include('layouts.body.aside')
        </aside>
        <!-- end::GXON Sidebar Menu -->

        <!-- begin::GXON Sidebar right -->
        <div class="app-sidebar-end">
            @include('layouts.body.app-sidebar-end')
        </div>
        <!-- end::GXON Sidebar right -->

        <main class="app-wrapper">
            @include('layouts.body.main')
        </main>

        <!-- begin::GXON Footer -->
        <footer class="footer-wrapper bg-body">
            @include('layouts.body.footer')
        </footer>
        <!-- end::GXON Footer -->

    </div>

    <!-- begin::GXON Page Scripts -->
    @include('layouts.js')
    <!-- end::GXON Page Scripts -->
</body>

</html>
