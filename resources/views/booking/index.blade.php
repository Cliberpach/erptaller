@extends('layouts.template')

@section('title')
    Egresos
@endsection

@section('css')
    <link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"
        integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .colorEditable {
            background-color: #d4edda !important;
        }

        .bg-info {
            background-color: #4FC3F7 !important;
            /* celeste medio */
            color: #000;
        }

        .bg-warning {
            background-color: #FFD54F !important;
            /* amarillo dorado suave */
            color: #000;
        }

        .bg-lightpurple {
            background-color: #CE93D8 !important;
            /* lila pastel */
            color: #000;
        }

        .bg-lightmint {
            background-color: #A5D6A7 !important;
            /* verde menta pastel */
            color: #000;
        }

        .list-group-item {
            min-height: 70px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
    </style>
@endsection

@section('content')
    @include('booking.modals.mdl_record_customer')
    @include('utils.spinners.spinner_1')

    <div class="card" style="background-color: #fafafa;">

        <div class="card-header">
            <form action="{{ route('tenant.reservas.reserva') }}" method="GET">
                <div class="d-flex justify-content-center align-items-center mb-3">
                    <div class="form-group">
                        <div class="d-flex align-items-center">
                            <input type="date" name="date" id="date" class="form-control me-2 text-center"
                                value="{{ $today }}">
                            <button type="submit" class="btn btn-rounded btn-primary">
                                <i class='bx bx-search-alt-2'></i>
                            </button>
                            <a href="{{ route('tenant.reservas.pdf', ['date' => request('date', $today)]) }}"
                                class="btn btn-danger btn-rounded" target="_blank">
                                <i class="fa-solid fa-file-pdf"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="card-body">
            @include('booking.list.list_fields')
        </div>

    @endsection

    @section('css')
        <style>
            .badge {
                font-size: 1.8125em !important;
            }
        </style>
    @endsection

    @section('js')
        <script>

            const schedulesBD       =   @json($schedules);
            const reservationsBD    =   @json($bookings);

            document.addEventListener('DOMContentLoaded', function() {

                events();
                console.log(schedulesBD);
                paintReservations(reservationsBD);
            });

            function events() {

                document.querySelectorAll('.watchBookingModal form').forEach(form => {
                    form.addEventListener('submit', (e) => {
                        e.preventDefault();

                        saveAttachments(e.target);
                    })
                })

                document.querySelectorAll('.cboNroHours').forEach(selectHoras => {
                    selectHoras.addEventListener('change', function() {
                        const precioHora = parseFloat(this.getAttribute('data-price'));
                        const hourId = this.getAttribute('data-hour');
                        const date = this.getAttribute('data-date');
                        const fieldId = this.getAttribute('data-field');


                        const paymentInput = document.querySelector(`#payment-${hourId}-${date}-${fieldId}`);
                        const creditCheckbox = document.querySelector(`#credit-${hourId}-${date}-${fieldId}`);

                        if (!paymentInput || !creditCheckbox) return;

                        const horasSeleccionadas = parseFloat(this.value);

                        if (creditCheckbox.checked) {
                            paymentInput.value = "0";
                            paymentInput.setAttribute("readonly", true);
                        } else {
                            if (!isNaN(precioHora) && !isNaN(horasSeleccionadas)) {
                                paymentInput.value = (precioHora * horasSeleccionadas).toFixed(2);
                                paymentInput.removeAttribute("readonly");
                            }
                        }
                    });
                });

                document.querySelectorAll('.form-check-input[name="credit"]').forEach(checkbox => {
                    checkbox.addEventListener('change', function() {
                        const idParts = this.id.split("-");
                        if (idParts.length < 5) return;

                        const hourId = idParts[1];
                        const date = `${idParts[2]}-${idParts[3]}-${idParts[4]}`;
                        const fieldId = idParts[5];

                        const paymentInput = document.querySelector(`#payment-${hourId}-${date}-${fieldId}`);
                        const horasSelect = document.querySelector(`#nro_horas-${hourId}-${date}-${fieldId}`);

                        if (!paymentInput || !horasSelect) return;

                        const precioHora = parseFloat(horasSelect.getAttribute('data-price'));
                        const horasSeleccionadas = parseFloat(horasSelect.value);

                        if (this.checked) {
                            paymentInput.value = "0";
                            paymentInput.setAttribute("readonly", true);
                        } else {
                            if (!isNaN(precioHora) && !isNaN(horasSeleccionadas)) {
                                paymentInput.value = (precioHora * horasSeleccionadas).toFixed(2);
                            } else {
                                paymentInput.value = "0";
                            }
                            paymentInput.removeAttribute("readonly");
                        }
                    });
                });

                document.querySelectorAll('.btn-save-booking').forEach(button => {

                    button.addEventListener('click', async function(e) {

                        mostrarAnimacion1();
                        button.disabled = true;
                        const form = button.closest('form');
                        const apiUrl = @json(route('reservations.store'));
                        const token = document.querySelector('input[name="_token"]').value;

                        let rucInput = form.querySelector('[name="ruc_number"]').value.trim();
                        let rucNumber = null;
                        let razonSocial = null;

                        if (rucInput.includes(" - ")) {
                            let partes = rucInput.split(" - ");
                            rucNumber = partes[0].trim(); // RUC
                            razonSocial = partes.slice(1).join(" - ").trim();
                        } else if (rucInput.length === 11) {
                            rucNumber = rucInput; // Si solo ingresó el RUC sin razón social
                        }

                        const creditCheckbox = form.querySelector('[name="credit"]');
                        const isCreditChecked = creditCheckbox && creditCheckbox.checked;

                        const data = {
                            document_number: form.querySelector('[name="document_number"]').value,
                            name: form.querySelector('[name="name"]').value,
                            phone: form.querySelector('[name="phone"]').value,
                            field_id: form.querySelector('[name="field_id"]').value,
                            schedule_id: form.querySelector('[name="schedule_id"]').value,
                            date: form.querySelector('[name="date"]').value,
                            payment_type: isCreditChecked ? "CREDITO" : form.querySelector(
                                '[name="payment_type"]').value,
                            payment: isCreditChecked ? "0" : form.querySelector('[name="payment"]')
                                .value,
                            nro_hours: form.querySelector('[name="nro_hours"]').value,
                            modality: form.querySelector('[name="modality"]').value,
                            ruc_number: rucNumber,
                            razon_social: razonSocial,
                            credit: isCreditChecked ? "1" :
                                "0" // Enviamos "1" si es crédito, "0" si no lo es
                        };

                        const formData = new FormData();
                        for (const key in data) {
                            formData.append(key, data[key]);
                        }

                        const hiddenInput = form.querySelector('[name="juntar_con_ids"]');
                        if (hiddenInput && hiddenInput.value) {
                            formData.append('juntar_con_ids', hiddenInput.value);
                        } else {
                            console.log('nulo');
                        }

                        console.log(hiddenInput);


                        const paymentType = form.querySelector('[name="payment_type"]').value;
                        const voucher = form.querySelector('[name="voucher"]').files[0];

                        // Si el tipo de pago no es efectivo, el voucher es obligatorio
                        if (paymentType !== 'EFECTIVO' && !voucher) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'El voucher es obligatorio para este tipo de pago.',
                                timer: 5000,
                                timerProgressBar: true
                            });
                            button.disabled = false;
                            ocultarAnimacion1();
                            return;
                        }

                        // Agregar el voucher solo si no es pago en efectivo y se seleccionó un archivo
                        if (paymentType !== 'EFECTIVO' && voucher) {
                            formData.append('voucher', voucher);
                        }

                        console.log('Datos del formulario enviados:', data);
                        clearValidationErrors('msgError');

                        try {

                            const response = await fetch(apiUrl, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                },
                                body: formData,
                            });

                            if (!response.ok) {
                                const errorData = await response.json();
                                if (response.status === 422) {
                                    throw {
                                        type: 'validation',
                                        errors: errorData.errors
                                    };
                                } else {
                                    throw {
                                        type: 'general',
                                        message: errorData.message || 'Error al procesar la solicitud.'
                                    };
                                }
                            }

                            const responseData = await response.json();

                            console.log('Reserva creada exitosamente:', responseData);
                            Swal.fire({
                                icon: 'success',
                                title: 'Reserva registrada',
                                text: 'La reserva ha sido registrada con éxito.',
                                timer: 3000,
                                timerProgressBar: true,
                                willClose: () => {
                                    location.reload();
                                },
                            });
                        } catch (error) {
                            console.error('Error al crear la reserva:', error);

                            if (error.type === 'validation') {
                                const hour_id = e.target.getAttribute('data-hour');
                                const date_id = e.target.getAttribute('data-date');
                                const field_id = e.target.getAttribute('data-field');
                                paintValidationErrors(error.errors,
                                    `${hour_id}_${date_id}_${field_id}_error`);
                            } else {
                                const errorMessage = error.message ||
                                    'Error al crear la reserva. Por favor, intenta nuevamente más tarde.';
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage,
                                    timer: 5000,
                                    timerProgressBar: true,
                                });
                            }
                        } finally {
                            ocultarAnimacion1();
                            button.disabled = false;
                        }

                    });
                });


            }

            function saveAttachments(formAttachments) {

                const swalWithBootstrapButtons = Swal.mixin({
                    customClass: {
                        confirmButton: "btn btn-success",
                        cancelButton: "btn btn-danger"
                    },
                    buttonsStyling: false
                });
                swalWithBootstrapButtons.fire({
                    title: "DESEA GUARDAR LOS CAMBIOS?",
                    text: "Se actualizarán los aditamentos de la reserva!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonText: "SÍ, GUARDAR!",
                    cancelButtonText: "NO, CANCELAR!",
                    reverseButtons: true
                }).then(async (result) => {
                    if (result.isConfirmed) {

                        Swal.fire({
                            title: 'Cargando...',
                            html: 'Guardando cambios...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        try {
                            const token = document.querySelector('input[name="_token"]').value;
                            const formData = new FormData(formAttachments);
                            const urlApiAttachments = '/api/reservations/attachments';

                            const response = await fetch(urlApiAttachments, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': token,
                                    'X-HTTP-Method-Override': 'PUT'
                                },
                                body: formData
                            });

                            const res = await response.json();

                            if (response.status === 422) {
                                if ('errors' in res) {
                                    //pintarErroresValidacion(res.errors);
                                }
                                Swal.close();
                                return;
                            }

                            if (res.success) {

                                //======= ACTUALIZAR TARGET RESERVATION =======
                                let attachments = ``;
                                const reservation_id = formData.get('reservation_id');
                                const divAttachments = document.querySelectorAll(
                                    `.reservation_attachment_${reservation_id}`);

                                if (formData.has('chkBall')) {
                                    const routeBall = @json(asset('assets\img\icons\icons_ld\pelota_futbol.png'));
                                    attachments += `<img src="${routeBall}" style="width:26px;" alt="">`;
                                }
                                if (formData.has('chkVest')) {
                                    const routeBall = @json(asset('assets\img\icons\icons_ld\chaleco.png'));
                                    attachments += `<img src="${routeBall}" style="width:26px;" alt="">`;
                                }
                                if (formData.has('chkDni')) {
                                    const routeBall = @json(asset('assets\img\icons\icons_ld\dni.png'));
                                    attachments += `<img src="${routeBall}" style="width:26px;" alt="">`;
                                }

                                divAttachments.forEach((da) => {
                                    da.innerHTML = attachments;
                                })

                                toastr.success(res.message, 'OPERACIÓN COMPLETADA');
                                Swal.close();

                                //======== CERRAR MODAL =======
                                const modalId = formAttachments.closest('.watchBookingModal').getAttribute('id');
                                $(`#${modalId}`).modal('toggle');

                            } else {
                                toastr.error(res.message, 'ERROR EN EL SERVIDOR');
                                Swal.close();
                            }

                        } catch (error) {
                            toastr.error(error, 'ERROR EN LA PETICIÓN GUARDAR ADITAMENTOS');
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

            function openBookingModal(title, hour, date, field) {
                const modalId = `#bookingModal-${hour}-${date}-${field}`;
                $(modalId).modal('toggle');
                $(`#title-modal-${hour}-${date}-${field}`).text(title);
                setupModalitySelect();
            }

            function openRentModal(title, hour, date, field, price) {
                const modalId = `#bookingModal-${hour}-${date}-${field}`;
                $(modalId).modal('toggle');
                $(`#title-modal-${hour}-${date}-${field}`).text(title);
                $(`#payment-${hour}-${date}-${field}`).val(price).prop('disabled', true);
            }

            function finishBooking(reservationId) {
                fetch(`/api/reservations/${reservationId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al obtener la reserva');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const modalId = `#finishBookingModal-${data.schedule.id}-${data.field.id}`;

                        $(modalId).modal('toggle');

                        // Llenar el modal con los datos
                        $(modalId).find(`#field-${data.schedule.id}-${data.field.id}`).val(data.field.field);
                        $(modalId).find(`#hour-${data.schedule.id}-${data.field.id}`).val(data.schedule.description);
                        $(modalId).find(`#date-${data.schedule.id}-${data.field.id}`).val(data.date);
                        $(modalId).find(`#document_number-${data.schedule.id}-${data.field.id}`).val(data.document_number);
                        $(modalId).find(`#name-${data.schedule.id}-${data.field.id}`).val(data.name);
                        $(modalId).find(`#phone-${data.schedule.id}-${data.field.id}`).val(data.phone);
                        $(modalId).find(`#modality-${data.schedule.id}-${data.field.id}`).val(data.modality);
                        $(modalId).find(`#old_payment-${data.schedule.id}-${data.field.id}`).val(data.payment);
                        $(modalId).find(`#old_payment_type-${data.schedule.id}-${data.field.id}`).val(data.payment_type);

                        const oldPayment = $(modalId).find(`#old_payment-${data.schedule.id}-${data.field.id}`).val();
                        const valorCampo = parseFloat($(modalId).find('#valorCampo').data('valor'));


                        if (isNaN(oldPayment)) {
                            throw new Error('El pago anterior no es un número válido.');
                        }

                        const nuevoPago = (valorCampo - oldPayment).toFixed(2);
                        console.log(nuevoPago);

                        $(modalId).find(`#payment-${data.schedule.id}-${data.field.id}`).val(nuevoPago);

                        if (data.payment_type !== 'EFECTIVO') {
                            $(modalId).find(`#voucher-container-${data.schedule.id}-${data.field.id}`).show();
                            $(modalId).find(`#voucher-${data.schedule.id}-${data.field.id}`).prop('disabled', false).val(
                                data.voucher);
                        } else {
                            $(modalId).find(`#voucher-container-${data.schedule.id}-${data.field.id}`).hide();
                            $(modalId).find(`#voucher-${data.schedule.id}-${data.field.id}`).prop('disabled', true);
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener la reserva:', error);
                        alert('Hubo un error al obtener los datos de la reserva. Por favor, inténtalo de nuevo.');
                    });
            }

            function watchBooking(reservationId) {
                mostrarAnimacion1();
                fetch(`/api/reservations/${reservationId}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error al obtener la reserva');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const modalId = `#watchBookingModal-${data.schedule.id}-${data.field.id}`;

                        $(modalId).modal('toggle');

                        // Llenar el modal con los datos
                        $(modalId).find(`#field-${data.schedule.id}-${data.field.id}`).val(data.field.field);
                        $(modalId).find(`#hour-${data.schedule.id}-${data.field.id}`).val(data.schedule.description);
                        $(modalId).find(`#date-${data.schedule.id}-${data.field.id}`).val(data.date);
                        $(modalId).find(`#document_number-${data.schedule.id}-${data.field.id}`).val(data.document_number);
                        $(modalId).find(`#name-${data.schedule.id}-${data.field.id}`).val(data.name);
                        $(modalId).find(`#phone-${data.schedule.id}-${data.field.id}`).val(data.phone);
                        $(modalId).find(`#payment-${data.schedule.id}-${data.field.id}`).val(data.total);

                        // Mostrar si fue crédito o no
                        const creditStatus = data.is_credit ? 'Reserva a Crédito' : 'Reserva Pagada';
                        const creditClass = data.is_credit ? 'alert-danger' : 'alert-success';
                        const creditContainer = $(modalId).find(`#credit-status-${data.schedule.id}-${data.field.id}`);

                        creditContainer.removeClass('alert-danger alert-success').addClass(creditClass).text(creditStatus);

                        // Manejar voucher
                        if (data.payment_type !== 'EFECTIVO') {
                            $(modalId).find(`#voucher-container-${data.schedule.id}-${data.field.id}`).show();
                            $(modalId).find(`#voucher-${data.schedule.id}-${data.field.id}`).prop('disabled', false).val(
                                data.voucher);
                        } else {
                            $(modalId).find(`#voucher-container-${data.schedule.id}-${data.field.id}`).hide();
                            $(modalId).find(`#voucher-${data.schedule.id}-${data.field.id}`).prop('disabled', true);
                        }
                    })
                    .catch(error => {
                        console.error('Error al obtener la reserva:', error);
                        alert('Hubo un error al obtener los datos de la reserva. Por favor, inténtalo de nuevo.');
                    }).finally(() => {
                        ocultarAnimacion1();
                    });
            }
        </script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            document.querySelectorAll('.btn-search').forEach(button => {
                button.addEventListener('click', async function() {
                    mostrarAnimacion1();
                    const form = button.closest('form');
                    const documentNumber = form.querySelector('[name="document_number"]').value;

                    if (!documentNumber) {
                        alert('Por favor, ingresa un número de documento.');
                        ocultarAnimacion1();
                        return;
                    }

                    button.disabled = true;
                    let client_exists = false;
                    let client_id = null;

                    try {
                        // Primera consulta: buscar en la base de datos local
                        const localResponse = await fetch(`/api/customers/${documentNumber}`);
                        const localData = await localResponse.json();

                        if (localData.data) {
                            // Si encuentra el cliente en la base de datos, llena los campos
                            form.querySelector('input[name="name"]').value = localData.data.name;
                            form.querySelector('input[name="phone"]').value = localData.data.phone;

                            client_id = localData.data.id;
                            client_exists = true;
                            ocultarAnimacion1();
                        } else {

                            mostrarAnimacion1();
                            // Si no encuentra el cliente en la base de datos, consulta la API de RENIEC
                            const url = `/landlord/dni/${documentNumber}`;

                            try {
                                const reniecResponse = await fetch(url, {
                                    method: 'GET',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'Accept': 'application/json'
                                    }
                                });

                                if (!reniecResponse.ok) {
                                    throw new Error(`HTTP error! status: ${reniecResponse.status}`);
                                }

                                const reniecData = await reniecResponse.json();

                                if (reniecData.success === false) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: 'DNI inválido o no existe en RENIEC!'
                                    });
                                } else {
                                    form.querySelector('input[name="name"]').value = reniecData.data
                                        .nombre_completo;
                                    // Swal.fire({
                                    //     icon: 'success',
                                    //     title: 'Éxito',
                                    //     text: 'Datos obtenidos correctamente de RENIEC.'
                                    // });
                                }
                            } catch (error) {
                                console.error('Error al consultar la API:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Hubo un problema al consultar la API de RENIEC. Por favor, intente nuevamente más tarde.'
                                });
                            }
                        }
                    } catch (error) {
                        console.error('Error al buscar el cliente en la base de datos:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al buscar el cliente en la base de datos.'
                        });
                    } finally {
                        button.disabled = false;
                        ocultarAnimacion1();
                    }
                });
            });
        </script>

        <script>
            document.querySelectorAll('.btn-search-ruc').forEach(button => {
                button.addEventListener('click', async function() {
                    mostrarAnimacion1();
                    const form = button.closest('form');
                    const rucInput = form.querySelector('[name="ruc_number"]');
                    let rucNumber = rucInput.value.trim();

                    if (!rucNumber || rucNumber.length !== 11) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'RUC inválido',
                            text: 'Por favor, ingresa un RUC válido de 11 dígitos.'
                        });
                        ocultarAnimacion1();
                        return;
                    }

                    button.disabled = true;

                    try {
                        // 1️⃣ Consultar en la base de datos local
                        const localResponse = await fetch(`/api/customers/ruc/${rucNumber}`);
                        if (localResponse.ok) {
                            const localData = await localResponse.json();
                            rucInput.value = `${localData.ruc_number} - ${localData.razon_social}`;
                            ocultarAnimacion1();
                            button.disabled = false;
                            return; // Salir de la función si encontró el RUC en la BD
                        }

                        // 2️⃣ Si no está en la BD, consultar en la API de SUNAT
                        const url = `/landlord/ruc/${rucNumber}`;
                        const sunatResponse = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });

                        if (!sunatResponse.ok) {
                            throw new Error(`HTTP error! status: ${sunatResponse.status}`);
                        }

                        const sunatData = await sunatResponse.json();

                        if (!sunatData.success) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'RUC inválido o no existe en SUNAT.'
                            });
                        } else {
                            // Mostrar RUC + Razón Social en el mismo input
                            rucInput.value =
                                `${sunatData.data.ruc} - ${sunatData.data.nombre_o_razon_social}`;
                        }
                    } catch (error) {
                        console.error('Error en la consulta:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al realizar la consulta. Intente nuevamente más tarde.'
                        });
                    } finally {
                        button.disabled = false;
                        ocultarAnimacion1();
                    }
                });
            });
        </script>

        <script></script>
        <script src="{{ asset('assets/js/utils.js') }}"></script>
    @endsection
