<script src="{{ asset('assets/libs/global/global.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/datatable.js') }}"></script>
<script src="{{ asset('assets/js/appSettings.js') }}"></script>

<script>
    window.lstSearchModules =   @json($lst_search_modules);
    const baseUrl           =   @json($base);

    lstSearchModules.forEach((item)=>{
        item.url    =   route(`${baseUrl}${item.url}`);
    })
</script>

<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.full.min.js"></script>
{{-- <script src="{{asset('assets/js/toast.js')}}"></script> --}}
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
