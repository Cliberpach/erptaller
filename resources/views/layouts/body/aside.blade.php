<div class="app-navbar-brand">
    <a class="navbar-brand-logo" href="index.html">
        <img src="{{ asset('assets/images/logo.svg') }}" alt="GXON Admin Dashboard Logo">
    </a>
    <a class="navbar-brand-mini visible-light" href="index.html">
        <img src="{{ asset('assets/images/logo-text.svg') }}" alt="GXON Admin Dashboard Logo">
    </a>
    <a class="navbar-brand-mini visible-dark" href="index.html">
        <img src="{{ asset('assets/images/logo-text-white.svg') }}" alt="GXON Admin Dashboard Logo">
    </a>
</div>

<nav class="app-navbar" data-simplebar>
    @include('layouts.body.aside.nav')
</nav>

<div class="app-footer">
    <a href="pages/faq.html" class="btn btn-outline-light waves-effect btn-shadow btn-app-nav w-100">
        <i class="fi fi-rs-interrogation text-primary"></i>
        <span class="nav-text">Help and Support</span>
    </a>
</div>
