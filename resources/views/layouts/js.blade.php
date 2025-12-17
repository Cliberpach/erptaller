  <script src="{{asset('assets/libs/global/global.min.js')}}"></script>
  <script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
  <script src="{{asset('assets/js/datatable.js')}}"></script>
  <script src="{{asset('assets/js/appSettings.js')}}"></script>
  <script src="{{asset('assets/js/main.js')}}"></script>

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
