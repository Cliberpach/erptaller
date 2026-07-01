<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        @include('layouts/body/aside')
        <div class="layout-page">

            @include('layouts/body/navbar')

            <div class="content-wrapper">
                <div class="container-xxl flex-grow-1 container-p-y">
                    @yield('content')
                </div>
                @include('layouts/body/footer')
            </div>
        </div>
    </div>
</div>
