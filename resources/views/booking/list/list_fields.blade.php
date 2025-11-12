<div class="row row-cols-1 row-cols-md-3 g-4 p-3">
    @foreach ($fields as $field)
        <div class="col">
            <h5 class="card-title text-center">{{ $field->field }}</h5>
            <div class="card mb-3">
                <ul class="list-group list-group-flush">
                    @foreach ($schedules as $schedule)
                        @php
                            // Separar el rango de horas y tomar solo la primera parte (hora de inicio)
                            $timeRange = explode(' - ', $schedule->description);
                            $scheduleTime = trim($timeRange[0]); // Hora inicial del rango

                            // Combinar la fecha y hora del horario para hacer la comparación completa
                            $scheduleDateTime = strtotime($today . ' ' . $scheduleTime);

                            // Sumar 30 minutos al tiempo de inicio del horario
                            $scheduleEndDateTime = strtotime('+30 minutes', $scheduleDateTime);

                            // Obtener la fecha y hora actuales
                            $currentDateTime = strtotime(now());

                            // Obtener el estado de la reserva
                            $reservation = $bookings->first(function ($booking) use ($today, $field, $schedule) {
                                $bookingStartTime = strtotime($booking->start_time);
                                $bookingEndTime = strtotime($booking->new_end_time);
                                $scheduleStartTime = strtotime($schedule->start_time);
                                $scheduleEndTime = strtotime($schedule->end_time);

                                if (date('H:i', $bookingEndTime) === '00:00') {
                                    $bookingEndTime = strtotime('24:00');
                                }
                                if (date('H:i', $scheduleEndTime) === '00:00') {
                                    $scheduleEndTime = strtotime('24:00');
                                }

                                return $booking->date === $today &&
                                    $booking->field_id === $field->id &&
                                    $bookingStartTime <= $scheduleStartTime &&
                                    $bookingEndTime >= $scheduleEndTime;
                            });

                            $reservationDetail = $bookingDetail
                                ->where('booking_id', optional($reservation)->id)
                                ->first();
                            $statusClass = '';

                            // Si hay una reserva, asignar la clase adecuada
                            if ($reservation) {
                                $modality = $reservation->modality;
                                $status = $reservation->status;

                                // Todos los estados comparten el color según modalidad
                                if (in_array($status, ['RESERVADO', 'ALQUILADO', 'ADICIONAL'])) {
                                    if ($modality === '7v7') {
                                        $statusClass = 'bg-warning'; // amarillo
                                    } elseif ($modality === '9v9') {
                                        $statusClass = 'bg-lightpurple'; // lila pastel
                                    } elseif ($modality === '11vs11') {
                                        $statusClass = 'bg-lightmint'; // naranja suave
                                    } else {
                                        $statusClass = 'bg-light';
                                    }
                                } else {
                                    $statusClass = 'bg-light'; // fallback
                                }
                            }

                            // Inhabilitar si han pasado más de 30 minutos desde el inicio del horario y no tiene un estado de RESERVADO o ALQUILADO
                            $isDisabled = false;
                            if (
                                $scheduleEndDateTime < $currentDateTime &&
                                (!$reservation ||
                                    !in_array($reservation->status, ['RESERVADO', 'ALQUILADO', 'ADICIONAL']))
                            ) {
                                $isDisabled = true;
                                $statusClass = 'bg-secondary';
                            }
                        @endphp

                        <li class="list-group-item {{ $statusClass }}">
                            <div class="d-flex justify-content-between align-items-center">

                                <div class="col-12">
                                    <div class="row justify-content-between">
                                        <div class="col-7">
                                            {{ $schedule->description }}
                                            @if ($reservation && $reservation->modality)
                                                <span
                                                    class="badge bg-primary ms-1">{{ strtoupper($reservation->modality) }}
                                                </span>
                                            @endif
                                        </div>

                                        <div class="col-5" style="display:flex;justify-content:end;">
                                            @if ($reservation)
                                                <div class="reservation_attachment_{{ $reservation->id }}">
                                                    @if ($reservation->ball)
                                                        <img src="{{ asset('assets\img\icons\icons_ld\pelota_futbol.png') }}"
                                                            style="width:26px;" alt="">
                                                    @endif
                                                    @if ($reservation->vest)
                                                        <img src="{{ asset('assets\img\icons\icons_ld\chaleco.png') }}"
                                                            style="width:26px;" alt="">
                                                    @endif
                                                    @if ($reservation->dni)
                                                        <img src="{{ asset('assets\img\icons\icons_ld\dni.png') }}"
                                                            style="width:26px;" alt="">
                                                    @endif
                                                </div>
                                            @endif
                                        </div>

                                    </div>

                                    @if ($reservation && in_array($reservation->status, ['RESERVADO', 'ALQUILADO']))
                                        @php
                                            $groupKey =
                                                $reservation->created_at .
                                                '_' .
                                                $reservation->customer_id .
                                                '_' .
                                                $reservation->date .
                                                '_' .
                                                $reservation->start_time;

                                            $grupo = $bookings->filter(function ($b) use ($reservation) {
                                                return $b->created_at == $reservation->created_at &&
                                                    $b->customer_id === $reservation->customer_id &&
                                                    $b->date === $reservation->date &&
                                                    $b->start_time === $reservation->start_time;
                                            });

                                            $totalPagado = $bookingDetail
                                                ->whereIn('booking_id', $grupo->pluck('id'))
                                                ->sum('payment');
                                            $totalEsperado = $grupo->sum(fn($b) => $b->nro_hours * $b->price_per_hour);
                                            $saldo = max(0, $totalEsperado - $totalPagado);

                                            if ($grupo->every(fn($r) => $r->status === 'ADICIONAL')) {
                                                $estado = 'Adicional';
                                            } elseif ($totalPagado >= $totalEsperado) {
                                                $estado = 'CANCELADO';
                                            } elseif ($grupo->contains('is_credit', true)) {
                                                $estado = 'CRÉDITO';
                                            } else {
                                                $estado = 'Saldo: S/' . number_format($saldo, 2);
                                            }
                                        @endphp

                                        <div class="row align-items-center"
                                            style="white-space: nowrap; overflow: hidden;">
                                            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 text-truncate">
                                                {{ $reservation->customer->name }}
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 text-truncate"
                                                style="text-align: right;">
                                                {{ $estado }}
                                            </div>
                                        </div>
                                    @endif

                                    @if ($reservation && $reservation->status === 'ADICIONAL')
                                        <div class="row align-items-center"
                                            style="white-space: nowrap; overflow: hidden;">
                                            <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12 text-truncate"
                                                style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                {{ $reservation->customer->name }}
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 text-truncate"
                                                style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: right;">
                                                Adicional
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                @if ($reservation && $reservation->status === 'ADICIONAL')
                                @else
                                    <div class="btn-group">
                                        <button type="button"
                                            class="btn btn-success btn-icon rounded-pill dropdown-toggle hide-arrow p-0"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bx bx-dots-vertical-rounded"></i>
                                        </button>

                                        <ul class="dropdown-menu dropdown-menu-end" style="">
                                            @if (!$isDisabled)
                                                @if ($reservation && $reservation->status === 'RESERVADO')
                                                    <li>
                                                        <button class="dropdown-item"
                                                            onclick="finishBooking({{ $reservation->id }})">Terminar
                                                            Reserva</button>
                                                    </li>
                                                    <li>
                                                        <a target="_blank"
                                                            href="{{ route('tenant.reservas.recibo', $reservation->id) }}"
                                                            class="dropdown-item">Mostrar Recibo</a>
                                                    </li>
                                                @elseif($reservation && $reservation->status === 'ALQUILADO')
                                                    <li>
                                                        <button class="dropdown-item"
                                                            onclick="watchBooking({{ $reservation->id }})">Ver</button>
                                                    </li>
                                                    <li>
                                                        <a target="_blank"
                                                            href="{{ route('tenant.reservas.recibo', $reservation->id) }}"
                                                            class="dropdown-item">Mostrar Recibo</a>
                                                    </li>
                                                @elseif ($reservation && $reservation->status === 'ADICIONAL')
                                                    <li>
                                                        <button class="dropdown-item text-muted" disabled>Campo
                                                            adicional</button>
                                                    </li>
                                                @else
                                                    <li>
                                                        <button class="dropdown-item"
                                                            onclick="openBookingModal('Reserva', {{ $schedule->id }}, '{{ $today }}', {{ $field->id }})">Reservar</button>
                                                    </li>
                                                    <li>
                                                        <button class="dropdown-item"
                                                            onclick="openRentModal('Alquiler', {{ $schedule->id }}, '{{ $today }}', {{ $field->id }}, {{ explode(' - ', $schedule->description)[0] >= $hourNight ? $field->night_price : $field->day_price }})">Alquilar</button>
                                                    </li>
                                                @endif
                                            @else
                                                <li>
                                                    <button class="dropdown-item" disabled>Horario pasado</button>
                                                </li>
                                            @endif

                                        </ul>
                                    </div>
                                @endif
                            </div>

                        </li>
                        @php
                            $first_hour = explode(' - ', $schedule->description)[0];
                        @endphp
                        @include('booking.booking-modal', [
                            'hour' => $schedule->description,
                            'hour_id' => $schedule->id,
                            'today' => $today,
                            'field' => $field,
                            'first_hour' => $first_hour,
                        ])
                        @if ($reservation)
                            @include('booking.finish-booking-modal', [
                                'schedule' => $schedule,
                                'field' => $field,
                                'today' => $today,
                                'reservation' => $reservation,
                                'first_hour' => $first_hour,
                                'nro_hours' => $reservation->nro_hours,
                                'total' => $reservation->total,
                            ])

                            @include('booking.watch-booking', [
                                'schedule' => $schedule,
                                'field' => $field,
                                'today' => $today,
                                'reservation' => $reservation,
                            ])
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>
    @endforeach
</div>
