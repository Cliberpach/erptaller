<div class="container">

    <div class="app-page-head">
        <h1 class="app-page-title">@yield('title')</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="index.html">Dashboard</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Datatable</li>
            </ol>
        </nav>
    </div>

    <div class="row">

        <div class="col-lg-12">

            @yield('content')

        </div>

    </div>

</div>
