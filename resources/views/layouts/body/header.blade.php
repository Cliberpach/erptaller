@php
    $host = request()->getHost();

    // Verificar si es localhost
    $isLocalhost = str_contains($host, 'localhost');

    // Partes del dominio
    $hostParts = explode('.', $host);

    // Detectar si es subdominio (solo si NO es localhost)
    $isSubdomain = !$isLocalhost && count($hostParts) > 2;

    $mostrarButton = false;

    if ($isLocalhost && count($hostParts) > 1) {
        $mostrarButton = true;
    }

    if (!$isLocalhost && count($hostParts) > 2) {
        $mostrarButton = true;
    }

@endphp

<div class="app-header-inner">
    <button class="app-toggler" type="button" aria-label="app toggler">
        <span></span>
        <span></span>
        <span></span>
    </button>
    <div class="app-header-start d-none d-md-flex">
        <form class="d-flex align-items-center h-100 w-lg-250px w-xxl-300px position-relative" action="#">
            <button type="button" class="btn btn-sm position-absolute start-0 ms-3 border-0 p-0">
                <i class="fi fi-rr-search"></i>
            </button>
            <input type="text" class="form-control rounded-5 ps-5" placeholder="Buscar algo" data-bs-toggle="modal"
                data-bs-target="#searchResultsModal">
        </form>
        {{-- <ul class="navbar-nav d-none d-xxl-flex flex-row gap-4">
            <li class="nav-item">
                <a class="nav-link" href="analytics.html">Reports & Analytics</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="pages/faq.html">Help</a>
            </li>
        </ul> --}}
    </div>
    <div class="app-header-end">
        <div class="px-lg-3 d-flex align-items-center px-2 ps-0">
            <div class="dropdown">
                <button class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative"
                    id="ld-theme" type="button" data-bs-auto-close="outside" aria-expanded="false"
                    data-bs-toggle="dropdown">
                    <i class="fi fi-rr-brightness scale-1x theme-icon-active"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center gap-2"
                            data-bs-theme-value="light" aria-pressed="false">
                            <i class="fi fi-rr-brightness scale-1x" data-theme="light"></i> Light
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center gap-2"
                            data-bs-theme-value="dark" aria-pressed="false">
                            <i class="fi fi-rr-moon scale-1x" data-theme="dark"></i> Dark
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center gap-2"
                            data-bs-theme-value="auto" aria-pressed="true">
                            <i class="fi fi-br-circle-half-stroke scale-1x" data-theme="auto"></i> Auto
                        </button>
                    </li>
                </ul>
            </div>
        </div>
        <div class="vr my-3"></div>
        <div class="d-flex align-items-center gap-sm-2 px-lg-4 px-sm-2 gap-0 px-1">

            @if ($mostrarButton)
                <div style="display:flex;flex-direction:column;justify-items:center;">
                    <a href="{{ route('tenant.taller.cotizaciones.create') }}"
                        class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </a>
                    <span style="font-size: 9px;">Cotización</span>
                </div>
                <div style="display:flex;flex-direction:column;align-items:center;">
                    <a href="{{ route('tenant.taller.ordenes_trabajo.create') }}"
                        class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light">
                        <i class="fas fa-tools"></i>
                    </a>
                    <span style="font-size: 9px;">Orden</span>
                </div>
            @endif
            {{-- <a href="email/inbox.html"
                class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light position-relative">
                <i class="fi fi-rr-envelope"></i>
                <span
                    class="position-absolute bg-danger border-3 border-light rounded-circle end-0 top-0 me-1 mt-1 border p-1">
                    <span class="visually-hidden">New alerts</span>
                </span>
            </a> --}}
            <div class="dropdown text-end">
                <button type="button" class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="true">
                    <i class="fi fi-rr-bell"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-lg-end w-300px mt-2 p-0">
                    <div class="border-bottom d-flex justify-content-between align-items-center px-3 py-3">
                        <h6 class="mb-0">Notifications <span
                                class="badge badge-sm rounded-pill bg-primary ms-2">9</span>
                        </h6>
                        <i class="bi bi-x-lg cursor-pointer"></i>
                    </div>
                    <div class="p-2" style="height: 300px;" data-simplebar>
                        <ul class="list-group list-group-hover list-group-smooth list-group-unlined">
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar avatar-xs avatar-status-success rounded-circle me-1">
                                    <img src="assets/images/avatar/avatar2.webp" alt="">
                                </div>
                                <div class="me-auto ms-2">
                                    <h6 class="mb-0">Emma Smith</h6>
                                    <small class="text-body d-block">Need to update the details.</small>
                                    <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">7 hr ago</small>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar avatar-xs bg-success rounded-circle text-white">D</div>
                                <div class="me-auto ms-2">
                                    <h6 class="mb-0">Design Team</h6>
                                    <small class="text-body d-block">Check your shared folder.</small>
                                    <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">6 hr ago</small>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar avatar-xs bg-dark rounded-circle text-white">
                                    <i class="fi fi-rr-lock"></i>
                                </div>
                                <div class="me-auto ms-2">
                                    <h6 class="mb-0">Security Update</h6>
                                    <small class="text-body d-block">Password successfully set.</small>
                                    <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">5 hr ago</small>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar avatar-xs bg-info rounded-circle text-white">
                                    <i class="fi fi-rr-shopping-cart"></i>
                                </div>
                                <div class="me-auto ms-2">
                                    <h6 class="mb-0">Invoice #1432</h6>
                                    <small class="text-body d-block">has been paid Amount: $899.00</small>
                                    <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">5 hr ago</small>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar avatar-xs bg-danger rounded-circle text-white">R</div>
                                <div class="me-auto ms-2">
                                    <h6 class="mb-0">Emma Smith</h6>
                                    <small class="text-body d-block">added you to Dashboard Analytics</small>
                                    <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">5 hr ago</small>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar avatar-xs avatar-status-success rounded-circle me-1">
                                    <img src="assets/images/avatar/avatar3.webp" alt="">
                                </div>
                                <div class="me-auto ms-2">
                                    <h6 class="mb-0">Olivia Clark</h6>
                                    <small class="text-body d-block">You can now view the “Report”.</small>
                                    <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">4 hr ago</small>
                                </div>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div class="avatar avatar-xs avatar-status-danger rounded-circle me-1">
                                    <img src="assets/images/avatar/avatar5.webp" alt="">
                                </div>
                                <div class="me-auto ms-2">
                                    <h6 class="mb-0">Isabella Walker</h6>
                                    <small class="text-body d-block">@Isabella please review.</small>
                                    <small class="text-muted position-absolute end-0 top-0 me-3 mt-2">2 hr ago</small>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="p-2">
                        <a href="javascript:void(0);" class="btn w-100 btn-primary waves-effect waves-light">View all
                            notifications</a>
                    </div>
                </div>
            </div>
            {{-- <a href="calendar.html" class="btn btn-icon btn-action-gray rounded-circle waves-effect waves-light">
                <i class="fi fi-rr-calendar"></i>
            </a> --}}
        </div>
        <div class="vr my-3"></div>
        <div class="dropdown ms-sm-3 ms-lg-4 ms-2 text-end">
            <a href="#" class="d-flex align-items-center py-2" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" aria-expanded="true">
                <div class="d-none d-lg-inline-block me-2 text-end">
                    <div class="fw-bold text-dark">{{ Auth::user()->name }}</div>
                    <small class="text-body d-block lh-sm">
                        <i class="fi fi-rr-angle-down text-3xs me-1"></i>
                        {{ DB::table('plans')->first()->description }} -
                        @foreach (Auth::user()->roles as $role)
                            {{ $role->name }}
                        @endforeach
                    </small>
                </div>
                <div class="avatar avatar-sm rounded-circle avatar-status-success">
                    <img src="{{ asset('assets/images/avatar/avatar1.webp') }}" alt="">
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end w-225px mt-1">
                <li class="d-flex align-items-center p-2">
                    <div class="avatar avatar-sm rounded-circle">
                        <img src="{{ asset('assets/images/avatar/avatar1.webp') }}" alt="">
                    </div>
                    <div class="ms-2">
                        <div class="fw-bold text-dark">{{ Auth::user()->name }} </div>
                        <small class="text-body d-block lh-sm">{{ Auth::user()->email }}</small>
                    </div>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="profile.html">
                        <i class="fi fi-rr-user scale-1x"></i> Ver Perfil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="task-management.html">
                        <i class="fi fi-rr-note scale-1x"></i> My Task
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="pages/faq.html">
                        <i class="fi fi-rs-interrogation scale-1x"></i> Help Center
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="settings.html">
                        <i class="fi fi-rr-settings scale-1x"></i> Account Settings
                    </a>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center gap-2" href="pages/pricing.html">
                        <i class="fi fi-rr-usd-circle scale-1x"></i> Upgrade Plan
                    </a>
                </li>
                <li>
                    <div class="dropdown-divider my-1"></div>
                </li>
                <li>
                    <a class="dropdown-item d-flex align-items-center text-danger gap-2" href="{{ route('logout') }}">
                        <i class="fi fi-sr-exit scale-1x"></i> Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
