<div class="modal fade" id="mdlCreateCustomer" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Registrar Cliente</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @include('utils.modals.customer.forms.form_create_customer')
            </div>
            <div class="modal-footer">

                <div class="col-12">

                    <div class="row">
                        <div class="col-12 d-flex justify-content-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                style="margin-right: 6px;">Cerrar</button>
                            <button class="btn btn-primary btnstoreCustomer" type="submit" form="formStoreCustomer">
                                <i class="fa-solid fa-floppy-disk"></i> Registrar
                            </button>
                        </div>

                        <div class="col-12">
                            <p style="display: block;margin:0;padding:0;font-weight:bold;" class="color_warning">Los
                                campos con (*) son obligatorios</p>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    let customerParams = {
        documentSearchCustomer: null
    };

    function iniciarSelect2() {
        $('.select2_form_customer').select2({
            theme: "bootstrap-5",
            width: $(this).data('width') ? $(this).data('width') : $(this).hasClass('w-100') ? '100%' : 'style',
            placeholder: $(this).data('placeholder'),
            dropdownParent: $('#mdlCreateCustomer'),
        });
    }

    function eventsMdlCreateCustomer() {
        iniciarSelect2();
        setDefaultData();

        document.querySelector('#formStoreCustomer').addEventListener('submit', (e) => {
            e.preventDefault();
            storeCustomer();
        })

        $('#mdlCreateCustomer').on('hidden.bs.modal', function() {

            //======= RESETEAR FORMULARIO ======
            $('.select2_form_customer').val(null).trigger('change');

            document.querySelector('#name').value = '';
            document.querySelector('#address').value = '';
            document.querySelector('#phone').value = '';
            document.querySelector('#email').value = '';

            $('#province').empty().trigger('change');
            $('#district').empty().trigger('change');

            $('#type_identity_document').val('1').trigger('change');

            customerParams.documentSearchCustomer   =   null;
            clearValidationErrors('msgErrorCustomer');

        });

        //======= CONSULTAR API DOCUMENTO DNI ========
        document.querySelector('#btn_search_nro_document').addEventListener('click', () => {

            const nro_document = document.querySelector('#nro_document').value;
            const type_identity_document = document.querySelector('#type_identity_document').value;
            toastr.clear();

            if (type_identity_document != 1 && type_identity_document != 3) {
                toastr.error('SOLO SE PUEDE CONSULTAR TIPO DE DOCUMENTO DNI Y RUC');
                return;
            }

            if (!nro_document) {
                toastr.error('DEBE INGRESAR UN NRO DE DOCUMENTO VÁLIDO');
                return;
            }

            if (type_identity_document == 1) {
                if (nro_document.length != 8) {
                    toastr.error('NRO DE DNI DEBE CONTAR CON 8 DÍGITOS');
                    return;
                }
            }

            if (type_identity_document == 2) {
                if (nro_document.length != 11) {
                    toastr.error('NRO DE RUC DEBE CONTAR CON 11 DÍGITOS');
                    return;
                }
            }

            consultDocument(type_identity_document, nro_document);

        })
    }

    function openMdlNewCustomer() {

        if (isNumeric(customerParams.documentSearchCustomer) && customerParams.documentSearchCustomer) {
            //====== DNI ======
            if (customerParams.documentSearchCustomer.length === 8) {
                $('#type_identity_document').val('1').trigger('change');
                document.querySelector('#nro_document').value = customerParams.documentSearchCustomer;
                document.querySelector('#btn_search_nro_document').click();
            }
            //========= RUC ========
            if (customerParams.documentSearchCustomer.length === 11) {
                $('#type_identity_document').val('3').trigger('change');
                document.querySelector('#nro_document').value = customerParams.documentSearchCustomer;
                document.querySelector('#btn_search_nro_document').click();
            }
        }

        $('#mdlCreateCustomer').modal('show');
    }

    //======= CONSULTAR DOCUMENTO IDENTIDAD =====
    async function consultDocument(type_identity_document, nro_document) {
        mostrarAnimacion1();
        try {
            const token = document.querySelector('input[name="_token"]').value;
            const urlConsultDocument =
                `{{ route('tenant.ventas.cliente.consult_document') }}?type_identity_document=${encodeURIComponent(type_identity_document)}&nro_document=${encodeURIComponent(nro_document)}`;

            const response = await fetch(urlConsultDocument, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': token
                },
            });

            const res = await response.json();

            if (res.success) {

                if (type_identity_document == 1) {
                    setDataDni(res.data);
                }

                if (type_identity_document == 3) {
                    setDataRuc(res.data);
                }

                toastr.info(res.message);
            } else {
                toastr.error(res.message, 'ERROR EN EL SERVIDOR AL CONSULTAR DOCUMENTO');
            }
        } catch (error) {
            toastr.error(error, 'ERROR EN LA PETICIÓN CONSULTAR DOCUMENTO');
        } finally {
            ocultarAnimacion1();
        }
    }

    function setDataDni(data) {

        const full_name = `${data.nombres} ${data.apellido_paterno} ${data.apellido_materno}`;
        const address = data.direccion;

        document.querySelector('#name').value = full_name;
        document.querySelector('#address').value = address;

    }

    function setDataRuc(data) {
        const nombre_o_razon_social = `${data.nombre_o_razon_social}`;
        const direccion_completa = data.direccion_completa;

        document.querySelector('#name').value = nombre_o_razon_social;
        document.querySelector('#address').value = direccion_completa;

        //======= ESTABLECIENDO UBIGEO =====
        const ubigeo = data.ubigeo;
        const ubigeo_department_id = ubigeo[0];
        const ubigeo_province_id = ubigeo[1];
        const ubigeo_district_id = ubigeo[2];

        if (!ubigeo_department_id || !ubigeo_province_id || !ubigeo_district_id) {
            toastr.info('NO SE OBTUVO EL UBIGEO!!!');
            return;
        }

        $('#department').val(ubigeo_department_id).trigger('change');
        $('#province').val(ubigeo_province_id).trigger('change');
        $('#district').val(ubigeo_district_id).trigger('change');

    }


    function changeTypeIdentityDocument(type_identity_document_id) {
        const inputNroDocument = document.querySelector('#nro_document');
        const btnSearchNroDocument = document.querySelector('#btn_search_nro_document');

        inputNroDocument.value = '';

        //======== DNI ========
        if (type_identity_document_id === '1') {
            inputNroDocument.maxLength = 8;
            inputNroDocument.disabled = false;
            inputNroDocument.classList.add('inputEnteroPositivo');
            btnSearchNroDocument.classList.remove('d-none');
        }

        //======== RUC =======
        if (type_identity_document_id === '3') {
            inputNroDocument.maxLength = 11;
            inputNroDocument.disabled = false;
            inputNroDocument.classList.add('inputEnteroPositivo');
            btnSearchNroDocument.classList.remove('d-none');
        }

        //======== CARNET EXTRANJERÍA U OTROS DOCUMENTOS =======
        if (type_identity_document_id === '2' || type_identity_document_id === '4' ||
            type_identity_document_id === '5' || type_identity_document_id === '6') {

            inputNroDocument.maxLength = 20;
            inputNroDocument.disabled = false;
            inputNroDocument.classList.remove('inputEnteroPositivo');
            btnSearchNroDocument.classList.add('d-none');

        }

    }

    function changeDepartment(department_id) {

        const lstProvinces = @json($provinces);
        const lstDistricts = @json($districts);
        let lstProvincesFiltered = [];

        if (department_id) {

            department_id = String(department_id).padStart(2, '0');

            lstProvincesFiltered = lstProvinces.filter((province) => {
                return province.department_id == department_id;
            })
            $('#province').empty().trigger('change');

            lstProvincesFiltered.forEach((province) => {
                $('#province').append(new Option(province.name, province.id, false, false));
            })

            $('#province').select2({
                theme: "bootstrap-5",
                placeholder: 'Seleccione una provincia',
                width: '100%',
                dropdownParent: $('#mdlCreateCustomer'),
            });

            $('#province').trigger('change');
        }

    }

    function changeProvince(province_id) {

        const lstDistricts = @json($districts);

        let lstDistrictsFiltered = [];

        if (province_id) {

            province_id = String(province_id).padStart(4, '0');

            lstDistrictsFiltered = lstDistricts.filter((district) => {
                return district.province_id == province_id;
            })

            $('#district').empty().trigger('change');

            lstDistrictsFiltered.forEach((district) => {
                $('#district').append(new Option(district.name, district.id, false, false));
            })

            $('#district').select2({
                theme: "bootstrap-5",
                placeholder: 'Seleccione un distrito',
                width: '100%',
                dropdownParent: $('#mdlCreateCustomer')
            });
        }

    }

    function storeCustomer() {
        const swalWithBootstrapButtons = Swal.mixin({
            customClass: {
                confirmButton: "btn btn-success",
                cancelButton: "btn btn-danger"
            },
            buttonsStyling: false
        });
        swalWithBootstrapButtons.fire({
            title: "DESEA REGISTRAR EL CLIENTE?",
            text: "Se creará un nuevo Cliente!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "SÍ, REGISTRAR!",
            cancelButtonText: "NO, CANCELAR!",
            reverseButtons: true
        }).then(async (result) => {
            if (result.isConfirmed) {

                clearValidationErrors('msgErrorCustomer');

                const token = document.querySelector('input[name="_token"]').value;
                const formStoreCustomer = document.querySelector('#formStoreCustomer');
                const formData = new FormData(formStoreCustomer);
                const urlStoreCustomer = @json(route('tenant.ventas.cliente.store'));

                Swal.fire({
                    title: 'Cargando...',
                    html: 'Registrando nuevo cliente...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const response = await fetch(urlStoreCustomer, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token
                        },
                        body: formData
                    });

                    const res = await response.json();

                    if (response.status === 422) {
                        if ('errors' in res) {
                            paintValidationErrors(res.errors, 'error_customer');
                        }
                        Swal.close();
                        return;
                    }

                    if (res.success) {

                        const customerNew = res.customer;
                        setCustomerNew(customerNew);

                        $('#mdlCreateCustomer').modal('hide');
                        toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                        Swal.close();
                    } else {
                        toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                        Swal.close();
                    }

                } catch (error) {
                    toastr.error(error, 'ERROR EN LA PETICIÓN REGISTRAR CLIENTE');
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


    function setCustomerNew(customerNew) {
        const option = {
            id: customerNew.id,
            full_name: `${customerNew.type_document_abbreviation}:${customerNew.document_number} - ${customerNew.name}`,
            email: customerNew.email ?? ''
        };

        if (!window.clientSelect.options[option.id]) {
            window.clientSelect.addOption(option);
        }

        window.clientSelect.setValue(option.id);

    }

    function setDefaultData() {
        const department_id = parseInt(@json($company_invoice->department_id));
        const province_id = parseInt(@json($company_invoice->province_id));
        const district_id = parseInt(@json($company_invoice->district_id));

        if (department_id && province_id && district_id) {
            $('#department').val(department_id).trigger('change');
            changeDepartment(department_id);

            $('#province').val(province_id).trigger('change');
            changeProvince(province_id);

            $('#district').val(district_id).trigger('change');
        }
    }
</script>
