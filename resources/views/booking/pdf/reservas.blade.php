<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Reservas - {{ $today }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
        }
        .container {
            width: 90%;
            margin: 30px auto;
        }
        h3 {
            color: #3a6ea5;
            font-size: 18px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: center;
        }
        th {
            background-color: #f4f8fc;
        }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #888;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <table style="width: 100%; border: none;">
        <tr>
            <td style="width: 20%;">
                <img src="{{ $company->logo_url }}" style="height: 80px; max-width: 120px;">
            </td>
            <td style="width: 60%;">
                <h2 style="margin: 0; font-size: 17px; color: #3a6ea5;">{{ $company->business_name }}</h2>
                <p style="margin: 0;">RUC: {{ $company->ruc }}</p>
                <p style="margin: 0;">{{ $company->fiscal_address }}</p>
                <p style="margin: 0;">Teléfono: {{ $company->phone }}</p>
                <p style="margin: 0;">EMAIL: {{ $company->email }}</p>
            </td>
            <td style="width: 20%;"></td>
        </tr>
    </table>

    <h3>Fecha de Reserva: {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}</h3>

    @php
        $mergedCells = [];
        $fieldsChunks = $fields->chunk(4);
        $groupEstados = [];

        foreach ($bookings as $booking) {
            $groupKey = $booking->created_at . '_' . $booking->customer_id . '_' . $booking->date . '_' . $booking->start_time;

            if (!isset($groupEstados[$groupKey])) {
                $grupoCompleto = $bookings->filter(function ($b) use ($booking) {
                    return $b->created_at == $booking->created_at &&
                           $b->customer_id === $booking->customer_id &&
                           $b->date === $booking->date &&
                           $b->start_time === $booking->start_time;
                });

                $totalPagado = $bookingDetail->whereIn('booking_id', $grupoCompleto->pluck('id'))->sum('payment');
                $totalEsperado = $grupoCompleto->sum(fn($b) => $b->nro_hours * $b->price_per_hour);
                $saldo = max(0, $totalEsperado - $totalPagado);

                if ($grupoCompleto->every(fn($b) => $b->status === 'ADICIONAL')) {
                    $estado = 'Adicional';
                } elseif ($totalPagado >= $totalEsperado) {
                    $estado = 'CANCELADO';
                } elseif ($grupoCompleto->contains('is_credit', true)) {
                    $estado = 'CRÉDITO';
                } else {
                    $estado = 'SALDO: S/ ' . number_format($saldo, 2);
                }

                $groupEstados[$groupKey] = [
                    'estado' => $estado,
                    'campos' => $grupoCompleto->map(fn($b) => $fields->firstWhere('id', $b->field_id)->field ?? '')->unique()->implode(' + ')
                ];
            }
        }
    @endphp

    @foreach ($fieldsChunks as $fieldChunk)
        <div style="page-break-inside: avoid;">
            <table>
                <thead>
                    <tr>
                        <th>Horario</th>
                        @foreach ($fieldChunk as $field)
                            <th>{{ $field->field }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($schedules as $schedule)
                        <tr>
                            <td>{{ $schedule->description }}</td>
                            @foreach ($fieldChunk as $field)
                                @php
                                    $reservation = $bookings->first(function ($b) use ($field, $schedule, $today) {
                                        return $b->date === $today &&
                                               $b->field_id === $field->id &&
                                               strtotime($b->start_time) <= strtotime($schedule->start_time) &&
                                               strtotime($b->new_end_time) > strtotime($schedule->start_time);
                                    });

                                    $cellKey = $field->id . '_' . $schedule->id;
                                @endphp

                                @if (isset($mergedCells[$cellKey]))
                                    @continue
                                @endif

                                @if ($reservation)
                                    @php
                                        $reservationStart = strtotime($reservation->start_time);
                                        $reservationEnd = strtotime($reservation->new_end_time);
                                        $rowspan = 1;

                                        foreach ($schedules as $next) {
                                            $nextStart = strtotime($next->start_time);
                                            if ($nextStart > $reservationStart && $nextStart < $reservationEnd) {
                                                $rowspan++;
                                                $mergedCells[$field->id . '_' . $next->id] = true;
                                            }
                                        }

                                        $groupKey = $reservation->created_at . '_' . $reservation->customer_id . '_' . $reservation->date . '_' . $reservation->start_time;
                                        $estadoData = $groupEstados[$groupKey] ?? ['estado' => '', 'campos' => ''];

                                        $mostrarEstado = $reservation->status !== 'ADICIONAL';
                                    @endphp

                                    <td rowspan="{{ $rowspan }}">
                                        <strong>{{ $reservation->customer->name ?? 'Sin cliente' }}</strong><br>
                                        <span>{{ $mostrarEstado ? $estadoData['estado'] : 'Adicional' }}</span>
                                    </td>
                                @else
                                    <td>-</td>
                                @endif
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <div class="footer">
        <p>&copy; {{ now()->year }} SISCOMFAC - Todos los derechos reservados</p>
    </div>
</div>
</body>
</html>
