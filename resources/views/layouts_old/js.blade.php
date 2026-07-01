<script src="{{ asset('assets/vendor/js/helpers.js') }}"></script>
<script src="{{ asset('assets/js/config.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
<script src="{{ asset('assets/vendor/js/menu.js') }}"></script>
<script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>
<script src="{{ asset('assets/js/toastr.min.js') }}"></script>
<script src="{{ asset('assets/js/pages-account-settings-account.js') }}"></script>
<script async defer src="https://buttons.github.io/buttons.js"></script>
<script>
    $(document).ready(function() {
        var currentUrl = window.location.href;
        var menuLinks = $('.menu-link');

        menuLinks.each(function() {
            var linkUrl = $(this).attr('href');
            if (currentUrl === linkUrl || (linkUrl && currentUrl.startsWith(linkUrl + "#")) || (
                    linkUrl && currentUrl.indexOf(linkUrl + "/") !== -1)) {
                $(this).addClass('active');
                $(this).parents('li.menu-item').addClass('active open');
                return false;
            }
        });
    });
</script>

{{-- <script src="https://code.jquery.com/jquery-3.7.0.js"></script> --}}

<!-- JQUERY -->
<script src="{{ asset('assets/jquery/jquery.js') }}"></script>

<!-- DATATABLES -->
<script src="{{ asset('assets/datatable/datatables.min.js') }}"></script>

{{-- <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script> --}}

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>

<script src="{{ asset('assets/js/utils.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        mostrarSessionMessages();
    })

    function mostrarSessionMessages() {
        const messageSuccess = "{{ Session::get('message_success') }}";
        const messageError = "{{ Session::get('message_error') }}";

        console.log(messageSuccess);
        if (messageSuccess) {
            Swal.fire({
                icon: 'success',
                title: 'OPERACIÓN COMPLETADA',
                text: messageSuccess,
                customClass: {
                    confirmButton: 'btn-primary'
                },
            });
        }

        if (messageError) {
            Swal.fire({
                icon: 'error',
                title: 'ERROR EN LA OPERACIÓN',
                text: messageError,
                customClass: {
                    confirmButton: 'btn-primary'
                },
            });
        }

    }
</script>

@yield('js')
