<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ route('tenant.home') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                {{-- <img src="#" alt="logo" class="w-px-40 h-auto rounded-circle"> --}}
                <img src="{{ asset('assets/img/logo.jpeg') }}" alt="" width="35">
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">TallerSuite</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large d-block d-xl-none ms-auto">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        @foreach ($modules as $module)
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle" data-toggle="module-{{ $module->id }}">
                    <i class="menu-icon tf-icons bx bx-dock-top"></i>
                    <div>{{ $module->description }}</div>
                </a>
                <ul class="menu-sub" id="module-{{ $module->id }}">
                    @foreach ($module->children as $child)
                        <li class="menu-item">
                            @if ($child->grandchildren->isNotEmpty())
                                <a href="javascript:void(0);" class="menu-link menu-toggle"
                                    data-toggle="child-{{ $child->id }}">
                                    <i class="menu-icon tf-icons bx bx-cube-alt"></i>
                                    <div>{{ $child->description }}</div>
                                </a>
                                <ul class="menu-sub" id="child-{{ $child->id }}">
                                    @foreach ($child->grandchildren as $grandchild)
                                        <li class="menu-item">
                                            <a href="{{ route($base . $grandchild->route_name) }}" class="menu-link">
                                                <div>{{ $grandchild->description }}</div>
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <a href="{{ route($base . $child->route_name) }}" class="menu-link">
                                    <div>{{ $child->description }}</div>
                                </a>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ul>
</aside>
