<div class="modal fade" id="searchResultsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header px-3 py-1">
                <form class="d-flex align-items-center position-relative w-100" action="#">
                    <button type="button" class="btn btn-sm position-absolute start-0 border-0 p-0 text-sm">
                        <i class="fi fi-rr-search"></i>
                    </button>
                    <input type="text" class="form-control form-control-lg border-0 ps-4 shadow-none"
                        id="searchInput" placeholder="Buscar algo">
                </form>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-2" style="height: 300px;" data-simplebar>
                <div id="recentlyResults">
                    <span class="text-uppercase text-2xs fw-semibold text-muted d-block mb-2">Búsquedas
                        recientes:</span>
                    <ul class="list-inline search-list">

                        @foreach ($modules as $module)
                            @foreach ($module->children as $child)
                                @if (!$child->grandchildren->isNotEmpty())
                                    <li>
                                        <a class="search-item" href="{{ route($base . $child->route_name) }}">
                                            <i class="fi fi-rr-file"></i> {{ $child->description }}
                                        </a>
                                    </li>
                                @endif
                            @endforeach
                        @endforeach


                        {{-- <li>
                            <a class="search-item" href="index.html">
                                <i class="fi fi-rr-apps"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a class="search-item" href="chat.html">
                                <i class="fi fi-rr-comment"></i> Chat
                            </a>
                        </li>
                        <li>
                            <a class="search-item" href="calendar.html">
                                <i class="fi fi-rr-calendar"></i> Calendar
                            </a>
                        </li>
                        <li>
                            <a class="search-item" href="chart/apexchart.html">
                                <i class="fi fi-rr-chart-pie-alt"></i> Apexchart
                            </a>
                        </li>
                        <li>
                            <a class="search-item" href="pages/pricing.html">
                                <i class="fi fi-rr-file"></i> Pricing
                            </a>
                        </li>
                        <li>
                            <a class="search-item" href="email/inbox.html">
                                <i class="fi fi-rr-envelope"></i> Email
                            </a>
                        </li> --}}
                    </ul>
                </div>
                <div id="searchContainer"></div>
            </div>
        </div>
    </div>
</div>
