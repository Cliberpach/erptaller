<div class="modal fade watchBookingModal" id="watchBookingModal-{{ $schedule->id }}-{{ $field->id }}" tabindex="-1" aria-labelledby="finishBookingModalLabel-{{ $schedule->id }}-{{ $field->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="watchBookingModalLabel-{{ $schedule->id }}-{{ $field->id }}">CAMPO ALQUILADO</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="watchBookingForm-{{ $schedule->id }}-{{ $field->id }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="reservation_id" value="{{$reservation->id}}">
                    
                    <div class="text-center">
                        <span class="h5 text-primary">{{ $field->field }}</span> 
                        <span class="h5 text-primary" id="valorCampo" data-valor="{{ $field->price }}"><strong>Monto: {{ $field->price }}</strong></span>
                        <input type="hidden" name="payment_finish-{{ $schedule->id }}-{{ $field->id }}" value="{{ $field->price }}">
                        <br>
                        Horario: {{ $schedule->description }} Fecha: {{ $today }}
                    </div>

                    <!-- Indicación de si fue un crédito -->
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">Tipo de Reserva</label>
                        <div class="alert {{ $reservation->is_credit ? 'alert-danger' : 'alert-success' }}" role="alert">
                            {{ $reservation->is_credit ? 'Reserva a Crédito' : 'Reserva Pagada' }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="document_number" class="form-label">Número de Documento</label>
                        <input type="text" class="form-control" id="document_number-{{ $schedule->id }}-{{ $field->id }}" name="document_number" value="{{ $reservation->customer->document_number }}" disabled>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                <label for="name" class="form-label">Nombre</label>
                                <input type="text" class="form-control" id="name-{{ $schedule->id }}-{{ $field->id }}" name="name" value="{{ $reservation->customer->name }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="phone" class="form-label">Celular</label>
                                <input type="text" class="form-control" id="phone-{{ $schedule->id }}-{{ $field->id }}" name="phone" value="{{ $reservation->customer->phone }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="payment" class="form-label">Pago Completado</label>
                                <input type="number" class="form-control" id="payment-{{ $schedule->id }}-{{ $field->id }}" name="payment" value="{{ $reservation->payment_amount }}" disabled>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <input class="form-check-input" type="checkbox" value="1" id="chkVest" name="chkVest" {{ $reservation->vest ? 'checked' : '' }}>
                            <label class="form-check-label" for="chkVest">
                              <img src="{{ asset('assets/img/icons/icons_ld/chaleco.png') }}" style="width:40px;" alt="">
                                CHALECO
                            </label>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <input class="form-check-input" type="checkbox" value="1" id="chkBall" name="chkBall" {{ $reservation->ball ? 'checked' : '' }}>
                            <label class="form-check-label" for="chkBall">
                              <img src="{{ asset('assets/img/icons/icons_ld/pelota_futbol.png') }}" style="width:40px;" alt="">
                                PELOTA
                            </label>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <input class="form-check-input" type="checkbox" value="1" id="chkDni" name="chkDni" {{ $reservation->dni ? 'checked' : '' }}>
                            <label class="form-check-label" for="chkDni">
                              <img src="{{ asset('assets/img/icons/icons_ld/dni.png') }}" style="width:40px;" alt="">
                                DNI
                            </label>
                        </div>
                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary" form="watchBookingForm-{{ $schedule->id }}-{{ $field->id }}">GUARDAR</button>
            </div>
        </div>
    </div>
</div>
