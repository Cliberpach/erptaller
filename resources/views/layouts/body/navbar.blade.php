<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-xl-0 d-xl-none me-3">
        <a class="nav-item nav-link me-xl-4 px-0" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

    @php
        $host = request()->getHost();
        $hostParts = explode('.', $host);
        $isSubdomain = count($hostParts) >= 2; // Si hay más de dos partes, es un subdominio
    print_r($hostParts);

    @endphp
    {{$host}}
    @if ($isSubdomain)
        <div class="d-flex align-items-center me-auto">
            <!-- Botón Nueva Reserva -->
            <a href="{{ route('tenant.reservas.reserva') }}"
                class="d-flex align-items-center text-decoration-none flex-row rounded px-3 py-2"
                style="solid #4c9e97; background: white; color: #4c9e97; font-weight: bold; transition: background 0.3s, transform 0.3s;">
                <i class="fas fa-plus me-2" style="font-size: 18px;"></i>
                <span style="font-size: 14px;">Nueva Reserva</span>
            </a>
        </div>
    @endif

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        <label for="" style="margin: 0 0 0 auto;font-weight: bold;">
            {{ DB::table('plans')->first()->description }}
        </label>
        <ul class="navbar-nav align-items-center flex-row">
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);" data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt class="w-px-40 rounded-circle h-auto" />
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="#">
                            <div class="d-flex">
                                <div class="me-3 flex-shrink-0">
                                    <div class="avatar avatar-online">
                                        <img src="{{ asset('assets/img/avatars/1.png') }}" alt
                                            class="w-px-40 rounded-circle h-auto" />
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="fw-semibold d-block">{{ Auth::user()->name }}</span>
                                    <small class="text-muted">
                                        @foreach (Auth::user()->roles as $role)
                                            {{ $role->name }}
                                        @endforeach
                                    </small>
                                </div>
                            </div>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <i class="bx bx-user me-2"></i>
                            <span class="align-middle">Mi perfil</span>
                        </a>
                    </li>
                    <li>
                        <div class="dropdown-divider"></div>
                    </li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}">
                            <i class="bx bx-power-off me-2"></i>
                            <span class="align-middle">Cerrar sesión</span>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
