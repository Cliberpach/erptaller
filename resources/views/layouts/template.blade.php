<!DOCTYPE html>
<html lang="en" class="light-style layout-menu-fixed" dir="ltr" data-theme="theme-default"
    data-assets-path="{{ asset('assets') }}" data-template="vertical-menu-template-free">

    <link rel="icon" type="image/x-icon" href="{{ asset('assets/favicon.ico') }}">

<head>
    @include('layouts/header')
</head>

<body>

     <div class="row">
            <div class="col-12">
                @include('utils.spinners.spinner_1')
            </div>
        </div>
        
    @include('layouts/body/body')
    @include('layouts/js')

</body>

<script>

    document.addEventListener('DOMContentLoaded',()=>{
        showAlertsPlan();
    })

    function showAlertsPlan(){
        toastr.clear();
        const hasMessageError = {{ Session::has('plan_md_error') ? 'true' : 'false' }};

        if (hasMessageError) {
            const message = {!! json_encode(Session::get('plan_md_error')) !!};
            toastr.error(message, 'OPERACIÓN INCORRECTA');

        }
    }

</script>

</html>
