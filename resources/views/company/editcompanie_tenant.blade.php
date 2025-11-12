@extends('layouts.template')

@section('title')
    Editar Empresa
@endsection

@section('css')
    <style>
        /* Estilos personalizados para el formulario */
        .form-label {
            font-weight: 600;
            color: #495057;
        }

        .form-control, .form-select {
            border-radius: 0.5rem;
            border-color: #ced4da;
            box-shadow: none;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-primary, .btn-secondary {
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        .logo-preview {
            display: block;
            max-height: 150px;
            margin-top: 10px;
            margin-bottom: 10px;
            border: 1px solid #ced4da;
            padding: 5px;
            border-radius: 0.5rem;
            object-fit: contain;
        }

        .remove-logo-btn {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #dc3545;
            cursor: pointer;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            font-size: 1.25rem;
            font-weight: bold;
        }

        .card-body {
            background-color: #ffffff;
        }

        .card {
            border-radius: 0.75rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .container-img {
            cursor: pointer;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 10px;
            border: 1px dashed #ced4da;
            border-radius: 0.5rem;
            margin-top: 10px;
            max-height: 200px;
            overflow: hidden;
            position: relative;
        }

        .container-img img {
            max-width: 100%;
            height: auto;
            border-radius: 0.5rem;
        }

        .delete-image {
            position: absolute;
            top: 10px;
            right: 10px;
            display: inline;
            font-size: 14px;
            color: #dc3545;
            background-color: #ffffff;
            padding: 2px 5px;
            border-radius: 0.25rem;
            cursor: pointer;
            border: 1px solid #dc3545;
            transition: background-color 0.3s, color 0.3s;
        }

        .delete-image:hover {
            background-color: #dc3545;
            color: #ffffff;
        }
    </style>


    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

@endsection

@section('content')

@include('utils.spinners.spinner_1')

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">

    <div class="col-lg-7 col-md-6 col-sm-12 col-xs-12 mb-3">
        <div class="card" style="height: 100%;">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center" style="color:white;">
                Datos Empresa
            </div>
            <div class="card-body">
                @include('company.forms.form_edit_company_tenant')
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-12">
                      <p style="display: block;margin:0;padding:0;font-weight:bold;" class="color_warning">Los campos con (*) son obligatorios</p>
                    </div>
                  </div>
            </div>
        </div>
    </div>

    <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12 mb-3">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center" style="color:white;">
                Facturación Credenciales
            </div>
            <div class="card-body">
                @include('company.forms.form_edit_billing_tenant')
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-12 d-flex justify-content-end">
                      <button class="btn btn-primary btnstoreCustomer" type="submit" form="form_edit_billing">
                        <i class="fa-solid fa-floppy-disk"></i> GUARDAR
                      </button>
                    </div>
      
                    <div class="col-12">
                      <p style="display: block;margin:0;padding:0;font-weight:bold;" class="color_warning">Los campos con (*) son obligatorios</p>
                    </div>
                  </div>
            </div>
        </div>
    </div>

    @include('company.modals.modal_numeration')
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 mb-3">
        <div class="card">
            <div class="card-header bg-primary d-flex justify-content-between align-items-center" style="color:white;">
                Facturación Numeración
            </div>
            <div class="card-body">
                <div class="row" style="margin-top:10px;margin-bottom:10px;">
                    <div class="col">
                        <button class="btn btn-primary" onclick="openMdlNumeration()">
                            <i class="fas fa-plus"></i> NUEVO
                        </button>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        @include('company.tables.tbl_numeration')
                    </div>
                </div>
                
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-12">
                      <p style="display: block;margin:0;padding:0;font-weight:bold;" class="color_warning">Los campos con (*) son obligatorios</p>
                    </div>
                  </div>
            </div>
        </div>
    </div>

    
</div>

@endsection

@section('js')
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAS6qv64RYCHFJOygheJS7DvBDYB0iV2wI&libraries=geometry,places"></script>

<script>

    let map;
    let onemarker = 0;

    document.addEventListener('DOMContentLoaded', function () {
        iniciarSelect2();
        startDataTableNumeration();
        setUbigeoPreview();
        setMapa();
        events(); 
    });

    function events(){

        eventsMdlNumeration();


        const inputLogo = document.getElementById('input-logo');
        const previewLogo = document.getElementById('preview-logo');
        //const containerImg = document.querySelector('.container-img');
        const nameImage = document.querySelector('.nameImage');
        //const deleteImageLink = document.querySelector('.delete-image');
        const urlImageDefault = '{{ asset('assets/img/products/img_default.png') }}';

        // Mostrar input file al hacer clic en la imagen
        // containerImg.addEventListener('click', () => {
        //     inputLogo.click();
        // });

        // Manejar la carga de la imagen
        inputLogo.addEventListener('change', () => {
            readImage(inputLogo, previewLogo);
        });

        // Quitar la imagen cargada y volver a la imagen por defecto
        // deleteImageLink.addEventListener('click', () => {
        //     previewLogo.src = urlImageDefault;
        //     inputLogo.value = ''; // Limpiar input file
        //     nameImage.textContent = 'img_default.png';
        //     deleteImageLink.style.display = 'none'; // Ocultar enlace de quitar imagen
        // });

        document.querySelector('#form_edit_billing').addEventListener('submit',(e)=>{
            e.preventDefault();
            updateInvoiceCompany();
        })

        document.getElementById("formEditCompanyTenant").addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                event.preventDefault(); 
            }
        });

    }

    function iniciarSelect2(){
        $( '.select2_form' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' )
        })

        $( '.select2_form_numeration' ).select2( {
            theme: "bootstrap-5",
            width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
            placeholder: $( this ).data( 'placeholder' ),
            dropdownParent: $('#mdlNumeration')
        })
    }

    function setMapa(){

        const lat   =   @json($company->lat);
        const lng   =   @json($company->lng);

        console.log('latitud',parseFloat(lat));

        map =   new google.maps.Map(document.getElementById("map"), {
                    zoom: 12,
                    center: {
                        lat: lat?parseFloat(lat):-8.1092027,
                        lng: lng?parseFloat(lng):-79.0244529
                    },
                    gestureHandling: "greedy",
                    zoomControl: false,
                    mapTypeControl: false,
                    streetViewControl: false,
                    fullscreenControl: false,
                });

        if(!isNaN(parseFloat(lat)) && !isNaN(parseFloat(lng))){
            editmarker(parseFloat(lat), parseFloat(lng))
        }

        google.maps.event.addListener(map, "click", function(event) {
            if (onemarker == 0) {
                var marker = new google.maps.Marker({
                    position: event.latLng,
                    map: map,
                    draggable: true
                });
                $("#lat").val(marker.getPosition().lat());
                $("#lng").val(marker.getPosition().lng());
                google.maps.event.addListener(marker, "dragend", function(event) {
                    $("#lat").val(this.getPosition().lat());
                    $("#lng").val(this.getPosition().lng());
                });
                onemarker = 1;
            }
        });

        const input = document.getElementById("searchBox");
        const autocomplete = new google.maps.places.Autocomplete(input);
        autocomplete.bindTo("bounds", map);


        autocomplete.addListener("place_changed", () => {
            const place = autocomplete.getPlace();

            if (!place.geometry || !place.geometry.location) {
                alert("No se encontró el lugar. Intenta con otra búsqueda.");
                return;
            }

            // Centrar el mapa en la ubicación seleccionada
            map.setCenter(place.geometry.location);
            map.setZoom(20);

            // Agregar marcador en la ubicación seleccionada
            
                var marker = new google.maps.Marker({
                    position: place.geometry.location,
                    map: map,
                    draggable: true
                });
                $("#lat").val(marker.getPosition().lat());
                $("#lng").val(marker.getPosition().lng());

                google.maps.event.addListener(marker, "dragend", function(event) {
                    $("#lat").val(this.getPosition().lat());
                    $("#lng").val(this.getPosition().lng());
                });

                onemarker = 1;
            
        });

    }

    function editmarker(lat, lng) {
        var marker = new google.maps.Marker({
            position: {
                lat: lat,
                lng: lng
            },
            map: map,
            draggable: true
        })
        $("#lat").val(marker.getPosition().lat());
        $("#lng").val(marker.getPosition().lng());
        google.maps.event.addListener(marker, "dragend", function(event) {
            $("#lat").val(this.getPosition().lat());
            $("#lng").val(this.getPosition().lng());
        });
        onemarker   =   1;
    }

    function setUbigeoPreview(){

        const departmentSelect = document.getElementById("department");
        if (departmentSelect) {
            departmentSelect.dispatchEvent(new Event("change"));
        }

        $('#province').val("{{ $company_invoice->province_id }}").trigger('change');
        $('#district').val("{{ $company_invoice->district_id }}").trigger('change');

    }

    function updateInvoiceCompany(){
        const swalWithBootstrapButtons = Swal.mixin({
        customClass: {
            confirmButton: "btn btn-success",
            cancelButton: "btn btn-danger"
        },
        buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
        title: "DESEA ACTUALIZAR LOS DATOS DE FACTURACIÓN DE LA EMPRESA?",
        text: "Esto producirá cambios en la facturación!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "SÍ, ACTUALIZAR!",
        cancelButtonText: "NO, CANCELAR!",
        reverseButtons: true
        }).then(async (result) => {
        if (result.isConfirmed) {

            Swal.fire({
                title: 'Cargando...',
                html: 'Actualizando facturación de la empresa...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading(); 
                }
            });


            try {

                clearValidationErrors('msgError');
                const token                     =   document.querySelector('input[name="_token"]').value;
                const form_edit_billing         =   document.querySelector('#form_edit_billing');
                const formData                  =   new FormData(form_edit_billing);

                const id                        =   @json($company->id);
                let urlUpdateInvoiceCompany     =   `{{ route('tenant.mantenimientos.empresas.updateInvoice', ['id' => ':id']) }}`;
                urlUpdateInvoiceCompany         =   urlUpdateInvoiceCompany.replace(':id', id);

                const response  =   await fetch(urlUpdateInvoiceCompany, {
                                        method: 'POST',
                                        headers: {
                                            'X-CSRF-TOKEN': token,
                                            'X-HTTP-Method-Override': 'PUT' 
                                        },
                                        body: formData
                                    });

                const   res =   await response.json();

                if(response.status === 422){
                    if('errors' in res){
                        paintValidationErrors(res.errors);
                    }
                    Swal.close();
                    return;
                }
                
                if(res.success){
                    toastr.success(res.message,'DATOS DE FACTURACIÓN ACTUALIZADOS');
                }else{
                    toastr.error(res.message,'ERROR EN EL SERVIDOR');
                }

            } catch (error) {
                toastr.error(error,'ERROR EN LA PETICIÓN ACTUALIZAR FACTURACIÓN DE LA EMPRESA');
            }finally{
                Swal.close();
            }

        } else if (result.dismiss === Swal.DismissReason.cancel) {
            swalWithBootstrapButtons.fire({
            title: "OPERACIÓN CANCELADA",
            text: "NO SE REALIZARON ACCIONES",
            icon: "error"
            });
        }
        });
    }

    // Función para mostrar la vista previa de la imagen cargada
    function readImage(inputRead, elementPreview) {
        if (inputRead.files && inputRead.files[0]) {
            const reader = new FileReader();
            reader.onload = function (r) {
                elementPreview.src = r.target.result;
                elementPreview.style.display = 'block';
                deleteImageLink.style.display = 'inline'; // Mostrar enlace de quitar imagen
            };

            reader.readAsDataURL(inputRead.files[0]);
            nameImage.textContent = inputRead.value.replace(/^.*[\\\/]/, '');

        } else {

            elementPreview.src = urlImageDefault;
            nameImage.textContent = 'img_default.png';
            deleteImageLink.style.display = 'none'; // Ocultar enlace de quitar imagen

        }
    }

function changeDepartment(department_id){

    const lstProvinces     =   @json($provinces);
    const lstDistricts     =   @json($districts);

    let lstProvincesFiltered      =   [];
        
    if(department_id){

        departamento_id = String(department_id).padStart(2, '0');

        lstProvincesFiltered      =   lstProvinces.filter((province)=>{
            return  province.department_id == department_id;
        })   

        $('#province').empty().trigger('change');

        lstProvincesFiltered.forEach((province)=>{
            $('#province').append(new Option(province.name, province.id, false, false));
        })

        $('#province').select2({
            theme: "bootstrap-5",
            placeholder: 'Seleccione una provincia',
            width: '100%'
        });

        $('#province').trigger('change');
    }

}

function changeProvince(province_id){

    const lstDistricts            =   @json($districts);

    let lstDistrictsFiltered      =   [];

    if(province_id){

        province_id = String(province_id).padStart(4, '0');

        lstDistrictsFiltered      =   lstDistricts.filter((district)=>{
            return  district.province_id == province_id;
        })   

        $('#district').empty().trigger('change');

        lstDistrictsFiltered.forEach((district)=>{
            $('#district').append(new Option(district.name, district.id, false, false));
        })

        $('#district').select2({
            theme: "bootstrap-5",
            placeholder: 'Seleccione un distrito',
            width: '100%'
        });
    }

}

</script>

<script src="{{asset('assets/js/utils.js')}}"></script>

@endsection