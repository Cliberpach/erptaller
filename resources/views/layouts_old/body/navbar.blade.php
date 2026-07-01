<nav class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-xl-0 d-xl-none me-3">
        <a class="nav-item nav-link me-xl-4 px-0" href="javascript:void(0)">
            <i class="bx bx-menu bx-sm"></i>
        </a>
    </div>

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

    @if ($mostrarButton)
        <style>
            .top-actions {
                display: flex;
                align-items: center;
                margin-left: 30px;
                /* SEPARA DEL ICONO DE MENU */
                gap: 22px;
                /* distancia entre botones */
            }

            .icon-wrapper {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-decoration: none;
                width: 60px;
            }

            .icon-btn {
                width: 42px;
                height: 42px;
                display: flex;
                align-items: center;
                justify-content: center;
                color: #4c9e97;
                font-size: 20px;
                border-radius: 10px;
                background: white;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
                transition: all 0.3s ease;
            }

            .icon-btn:hover {
                transform: translateY(-4px) scale(1.08);
                background: #4c9e97;
                color: white;
                box-shadow: 0 5px 12px rgba(0, 0, 0, 0.2);
            }

            .icon-text {
                font-size: 11px;
                font-weight: bold;
                margin-top: 5px;
                color: #4c9e97;
                text-align: center;
                line-height: 1.1;
            }

            @media (max-width: 768px) {
                .top-actions {
                    gap: 10px;
                    margin-left: 10px;
                }
            }
        </style>

        <div class="top-actions">

            <!-- COTIZACIÓN -->
            <a href="{{ route('tenant.taller.cotizaciones.create') }}" class="icon-wrapper">
                <div class="icon-btn">
                    <i class="fas fa-file-invoice"></i>
                </div>
                <span class="icon-text">Cotización</span>
            </a>

            <!-- ORDEN DE TRABAJO -->
            <a href="{{ route('tenant.taller.ordenes_trabajo.create') }}" class="icon-wrapper">
                <div class="icon-btn">
                    <i class="fas fa-tools"></i>
                </div>
                <span class="icon-text">Orden Trabajo</span>
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
