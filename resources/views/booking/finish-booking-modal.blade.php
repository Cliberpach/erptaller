<div class="modal fade" id="finishBookingModal-{{ $schedule->id }}-{{ $field->id }}" tabindex="-1" aria-labelledby="finishBookingModalLabel-{{ $schedule->id }}-{{ $field->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="finishBookingModalLabel-{{ $schedule->id }}-{{ $field->id }}">Terminar Reserva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="finishBookingForm-{{ $schedule->id }}-{{ $field->id }}" enctype="multipart/form-data">
                    @csrf
                    <div class="text-center">
                        <span class="h5 text-primary">{{ $field->field }}</span> 
                        <span class="h5 text-primary" id="valorCampo" data-valor="{{$total}}">
                            <p class="text-primary mt-1">
                                Modalidad: <strong>{{ $reservation->modality }}</strong>
                            </p>
                            <p class="text-primary mt-1">
                                Campo(s): <strong>{{ $reservation->field_names }}</strong>
                            </p>
                            <strong>Monto: {{$first_hour >= $hourNight?$field->night_price:$field->day_price}}</strong></span>
                        <input type="hidden" name="payment_finish-{{ $schedule->id }}-{{ $field->id }}" value="{{ $field->price }}">
                        <input type="hidden" id="date-{{ $schedule->id }}-{{ $field->id }}" value="{{ $today }}">
                        <br>
                        Horario: {{ $schedule->description }} Fecha: {{ $today }}
                    </div>
                    
                    <div class="mb-3">
                        <label for="document_number" class="form-label">Número de Documento</label>
                        <input type="text" class="form-control" id="document_number-{{ $schedule->id }}-{{ $field->id }}" name="document_number" disabled>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="name-{{ $schedule->id }}-{{ $field->id }}" name="name" disabled>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="phone" class="form-label">Celular</label>
                                <input type="text" class="form-control" id="phone-{{ $schedule->id }}-{{ $field->id }}" name="phone" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="payment" class="form-label">Pago Anterior</label>
                                <input type="number" class="form-control" id="old_payment-{{ $schedule->id }}-{{ $field->id }}" name="payment" disabled>
                            </div>
                        </div>
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                            <label for="nro_horas-{{ $schedule->id }}-{{ $field->id }}" style="font-weight: bold;">N° Horas</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <i class="fas fa-stopwatch"></i>
                                </span>
                                <input readonly value="{{$nro_hours}}" class="form-control" id="nro_horas-{{ $schedule->id }}-{{ $field->id }}"  name="nro_hours">  
                                </input>                                
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="modality" id="modality-{{ $schedule->id }}-{{ $field->id }}">


                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="payment_type" class="form-label">Tipo de pago</label>
                                <select class="form-control" id="payment_type-{{ $schedule->id }}-{{ $field->id }}" name="payment_type">
                                    <option value="EFECTIVO">Efectivo</option>
                                    <option value="YAPE">Yape</option>
                                    <option value="PLIN">Plin</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="payment" class="form-label">Pago Faltante</label>
                                <input type="number" class="form-control" id="payment-{{ $schedule->id }}-{{ $field->id }}" name="payment">
                            </div>
                            <p class="msgError payment_{{ $schedule->id }}_{{ $field->id }}_error" style="margin:0;padding:0;color:red;font-weight:bold;"></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group">
                            <label for="voucher">Voucher</label>
                            <input type="file" class="form-control" name="voucher" id="voucher-{{ $schedule->id }}-{{ $field->id }}">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="finishBookingSave({{ $schedule->id }},{{ $field->id }} )">Guardar</button>
            </div>
        </div>
    </div>
</div>


<script>
    function finishBookingSave(scheduleId, fieldId) {
    const modalId = `#finishBookingModal-${scheduleId}-${fieldId}`;
    const apiUrl = `/api/reservations`;
    
    // Obtén todos los campos necesarios para la reserva
    const payment           =   parseFloat($(modalId).find(`#payment-${scheduleId}-${fieldId}`).val());
    const paymentType       =   $(modalId).find(`#payment_type-${scheduleId}-${fieldId}`).val();
    const voucherFile       =   $(modalId).find(`#voucher-${scheduleId}-${fieldId}`)[0].files[0];
    const documentNumber    =   $(modalId).find(`#document_number-${scheduleId}-${fieldId}`).val();
    const name              =   $(modalId).find(`#name-${scheduleId}-${fieldId}`).val();
    const phone             =   $(modalId).find(`#phone-${scheduleId}-${fieldId}`).val();
    const modality            =   $(modalId).find(`#modality-${scheduleId}-${fieldId}`).val();
    const fieldIdValue      =   fieldId;
    const scheduleIdValue   =   scheduleId;
    const date              =   $(modalId).find(`#date-${scheduleId}-${fieldId}`).val();
    const nro_hours         =   document.querySelector(`#nro_horas-${scheduleId}-${fieldId}`).value;  

    // Inicializar el FormData y agregar todos los datos requeridos
    const formData = new FormData();
    formData.append('payment', payment);
    formData.append('payment_type', paymentType);
    formData.append('document_number', documentNumber);
    formData.append('name', name);
    formData.append('modality', modality);
    formData.append('phone', phone);
    formData.append('field_id', fieldIdValue);
    formData.append('schedule_id', scheduleIdValue);
    formData.append('date', date);
    formData.append('nro_hours', nro_hours);


    // Validar si se requiere el voucher y no ha sido seleccionado
    if (paymentType !== 'EFECTIVO' && !voucherFile) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Este método de pago requiere un voucher.',
            confirmButtonText: 'Aceptar'
        });
        return; // Detener el proceso si no se adjunta el voucher
    }

    // Agregar el voucher solo si no es pago en efectivo y se seleccionó un archivo
    if (paymentType !== 'EFECTIVO' && voucherFile) {
        formData.append('voucher', voucherFile);
    }

    clearValidationErrors('msgError');

    // Mostrar un mensaje de espera
    Swal.fire({
        title: 'Guardando...',
        text: 'Por favor, espera mientras se guarda la información.',
        icon: 'info',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    // Enviar los datos al backend
    fetch(apiUrl, {
        method: 'POST',
        body: formData,
    })
    .then(response => {

        if (!response.ok) {
                        
            if(response.status === 422){
                           
                return response.json().then(errors => {
                    throw errors;
                });
            }else{
                return response.json().then(errorData => {
                    throw errorData;
                });            
            }

        }
        return response.json(); 
    })
    .then(response => {
        console.log('Reserva creada exitosamente:', response);
        Swal.fire({
            icon: 'success',
            title: 'Reserva registrada',
            text: 'La reserva ha sido registrada con éxito.',
            timer: 3000,
            timerProgressBar: true,
            willClose: () => {
                location.reload();
            }
        });
    })
    .catch(error => {
        console.error('Error al crear la reserva:', error);

        if('errors' in error){
            paintValidationErrors(error.errors,`${scheduleId}_${fieldId}_error`);
            Swal.close();
        }else{
            const errorMessage = error.message || 'Error al crear la reserva. Por favor, intenta nuevamente más tarde.';
            console.error(errorMessage);
            console.log(error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorMessage,
                timer: 5000,
                timerProgressBar: true
            }); 
        }

        // Swal.fire({
        //     icon: 'error',
        //     title: 'Error',
        //     text: '12Hubo un error al crear la reserva: ' + error.message,
        //     confirmButtonText: 'Aceptar',
        //     allowOutsideClick: false, 
        //     allowEscapeKey: false, 
        // });
    });
}

</script>