<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Reserva</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 90%; /* Aumentamos el ancho del contenedor */
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .details p, .totals p {
            margin: 5px 0;
            font-size: 9px;
        }

        h3 {
            margin-top: 40px;
            color: #3a6ea5;
            text-align: left;
            font-size: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .table-info {
            width: 100%;
            table-layout: auto; /* Cambio aquí: 'auto' permite que las tablas ocupen más espacio */
            word-wrap: break-word;
            border-collapse: collapse;
            margin-bottom: 30px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-info, .table-info th, .table-info td {
            border: 1px solid #ddd;
        }

        .table-info th {
            background-color: #f4f8fc;
            font-weight: bold;
            padding: 6px;
            font-size: 9px;
        }

        .table-info td {
            padding: 6px;
            text-align: center;
            font-size: 9px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
            color: #888;
        }

        .totals {
            background-color: #f4f8fc;
            padding: 6px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .totals p {
            font-size: 9px;
            font-weight: bold;
            margin: 10px 0;
        }


    /* TABLE TOTALES  */
    .table-info-totales {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .table-info-totales th, .table-info-totales td {
        border: 1px solid #ddd;
        padding: 6px;
        text-align: left;
        font-size: 9px;
    }

    .table-info-totales th {
        background-color: #f4f8fc;
        font-weight: bold;
    }

    .table-info-totales td {
        font-size: 9px;
    }

    </style>
</head>
<body>
    <div class="container">

        <table style="width: 100%; border: 0; cellpadding: 0; cellspacing: 0;">
            <tr>
                <!-- Columna 1: Imagen -->
                <td style="width: 20%; text-align: left;">
                    <img src="{{ $company->logo_url }}" alt="Logo" style="height: 100px;object-fit:contain;max-width:120px;">
                </td>
        
                <!-- Columna 2: Información de la empresa -->
                <td style="width: 60%; text-align: left;">
                    <h2 style="margin: 0; font-size: 17px; color: #3a6ea5;">{{ $company->business_name }}</h2>
                    <p style="margin: 0; font-size: 14px; color: #555;">RUC: {{ $company->ruc }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">{{ $company->fiscal_address }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Teléfono: {{ $company->phone }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">EMAIL: {{ $company->email }}</p>
                </td>

                <!-- Columna 3: Vacía -->
                <td style="width: 20%;"></td>
            </tr>
        </table>
        
        
        <table class="table-info" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">Cajero:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">{{$cajero->name}}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">Caja:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">{{$caja->pettyCash->name}}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">Turno:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">{{$caja->shift->time}}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">Monto Inicial:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">{{$caja->initial_amount}}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">Fecha y Hora de Apertura:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">{{ \Carbon\Carbon::parse($caja->initial_date)->format('d/m/Y H:i') }}</td>
            </tr>
        </table>
        

        <h5 style="font-size:9px;">RESERVAS DEL DÍA</h5>

        <table class="table-info">
            <thead>
                <tr>
                    <th>NÚMERO</th>
                    <th>CLIENTE</th>
                    <th>MONTO</th>
                    <th>EFECTIVO</th>
                    <th>TRANSFERENCIA</th>
                    <th>YAPE/PLIN</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedReservations = $reservations->groupBy('booking_id');
                @endphp

                @foreach($groupedReservations as $bookingId => $reservationGroup)
                    @php
                        // Inicializar variables para sumar los pagos por tipo
                        $totalMonto = 0;
                        $totalEfectivo = 0;
                        $totalTransferencia = 0;
                        $totalYapePlin = 0;

                        // Iterar sobre cada grupo de reservaciones y sumar los pagos por tipo
                        foreach ($reservationGroup as $reservation) {
                            $totalMonto += $reservation->payment;

                            if ($reservation->payment_type == 'EFECTIVO') {
                                $totalEfectivo += $reservation->payment;
                            } elseif ($reservation->payment_type == 'TRANSFERENCIA') {
                                $totalTransferencia += $reservation->payment;
                            } elseif ($reservation->payment_type == 'YAPE' || $reservation->payment_type == 'PLIN') {
                                $totalYapePlin += $reservation->payment;
                            }
                        }
                    @endphp
                    <tr>
                        <td>{{ $bookingId }}</td>
                        <td>{{ $reservationGroup->first()->booking->customer->name }}</td> <!-- Usar el primer elemento del grupo para el nombre del cliente -->
                        <td>{{ number_format($totalMonto, 2) }}</td>
                        <td>{{ number_format($totalEfectivo, 2) }}</td>
                        <td>{{ number_format($totalTransferencia, 2) }}</td>
                        <td>{{ number_format($totalYapePlin, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

<!-- Totales -->
<table class="table-info-totales">
    <thead>
        <tr>
            <th>Totales</th>
            <th>Monto</th>
        </tr>
    </thead>
    <tbody>
        @php
            $total_efectivo = $reservations->where('payment_type', 'EFECTIVO')->sum('payment');
            $total_transferencia = $reservations->where('payment_type', 'TRANSFERENCIA')->sum('payment');
            $total_yape_plin = $reservations->whereIn('payment_type', ['YAPE', 'PLIN'])->sum('payment');
            $total_general = $reservations->sum('payment');
        @endphp
        <tr>
            <td><strong>Total Efectivo:</strong></td>
            <td>{{ number_format($total_efectivo, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Transferencia:</strong></td>
            <td>{{ number_format($total_transferencia, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Yape/Plin:</strong></td>
            <td>{{ number_format($total_yape_plin, 2) }}</td>
        </tr>
        <tr>
            <td><strong>Total Reservas:</strong></td>
            <td>{{ number_format($total_general, 2) }}</td>
        </tr>
    </tbody>
</table>

        <h5 style="font-size:9px;">VENTAS DEL DÍA</h5>

        <table class="table-info">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>CLIENTE</th>
                    <th>MONTO</th>
                    @foreach ($payment_methods as $payment_method)
                        <th>{{ $payment_method->description }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php
                    // Inicializar variables para almacenar el total por tipo de pago
                    $totals = [];
                    foreach ($payment_methods as $payment_method) {
                        $totals[$payment_method->id] = 0;
                    }
                @endphp
        
                @foreach($sale_documents as $sale)
                    <tr>
                        <td>{{ $sale->serie . '-' . $sale->correlative }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td>{{ number_format($sale->total, 2) }}</td>
        
                        @foreach ($payment_methods as $payment_method)
                            <td>
                                @if ($sale->method_pay_id_1 == $payment_method->id)
                                    {{ number_format($sale->amount_pay_1, 2) }}
                                    @php
                                        $totals[$payment_method->id] += $sale->amount_pay_1;
                                    @endphp
                                @elseif ($sale->method_pay_id_2 == $payment_method->id)
                                    {{ number_format($sale->amount_pay_2, 2) }}
                                    @php
                                        $totals[$payment_method->id] += $sale->amount_pay_2;
                                    @endphp
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <table class="table-info-totales">
            <thead>
                <tr>
                    <th>Totales</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payment_methods as $payment_method)
                    <tr>
                        <td><strong>Total {{ $payment_method->description }}:</strong></td>
                        <td>{{ number_format($totals[$payment_method->id], 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td><strong>Total Ventas:</strong></td>
                    <td>{{ number_format($sale_documents->sum('total'), 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ now()->year }} {{ $company->business_name }} - Todos los derechos reservados</p>
        </div>
       
      
    </div>
</body>
</html>
