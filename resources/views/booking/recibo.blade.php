<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo de Reserva</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%; /* Ajustado al 100% del ancho del papel */
            max-width: 226.77pt; /* 80 mm en puntos */
            margin: 0 auto;
            padding: 10px;
            background-color: #fff;
            border-radius: 5px;
            text-align: center;
        }

        .header {
            margin-bottom: 10px;
        }

        .header h2 {
            font-size: 16px; /* Tamaño de fuente más pequeño para que encaje bien */
            margin: 0;
        }

        .header p {
            font-size: 10px;
            margin: 2px 0; /* Espacio más ajustado */
            color: #666;
        }

        .logo {
            width: 80px; /* Ajusta el tamaño del logo según sea necesario */
            margin-bottom: 10px;
        }

        .details {
            margin-bottom: 10px;
            font-size: 11px;
            text-align: left;
        }

        .details p {
            margin: 3px 0;
            font-weight: bold;
            font-size: 10px;
        }

        .details p span {
            font-weight: normal;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 10px;
        }

        table th, table td {
            padding: 5px;
            text-align: left;
            border: 1px solid #ddd;
        }

        table th {
            background-color: #f8f8f8;
            font-weight: bold;
            font-size: 10px;
        }

        table td {
            font-size: 10px;
            text-align: center; /* Centramos los textos dentro de las celdas */
        }

        .total {
            font-size: 11px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .total p {
            margin: 5px 0;
        }

        .total span {
            color: #333;
        }

        .highlight {
            color: #d9534f;  /* Rojo suave para destacar */
        }

        .footer {
            font-size: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header con detalles de la empresa -->
        <div class="header">
            <!-- Mostrar el logo si existe -->
            <img src="{{ $company->base64_logo }}" alt="Logo de la Empresa" class="logo">




            <h2>{{ $company->business_name }}</h2>
            <p>RUC: {{ $company->ruc }}</p>
            <p>{{ $company->fiscal_address }}</p> <!-- Dirección fiscal -->
            <p>Teléfono: {{ $company->phone }}</p> <!-- Teléfono de la empresa -->
            <p>Celular: {{ $company->cellphone }}</p> <!-- Celular de la empresa -->
            <p><strong>Recibo de Reserva</strong></p>
        </div>

        <!-- Detalles de la reserva -->
        <div class="details">
            <p><strong>Fecha de Reserva:</strong> <span>{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</span></p>
            <p><strong>Cliente:</strong> <span>{{ $reservation->customer->name }}</span></p>
            <p><strong>DNI:</strong> <span>{{ $reservation->customer->document_number }}</span></p>
        </div>

        <!-- Tabla con detalles de la reserva -->
        <table>
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th>Fecha de Pago</th>
                    <th>Total (S/)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reservation_detail as $detail)
                    <tr>
                        <td>{{ $reservation->field->field }}</td>
                        <td>{{ \Carbon\Carbon::parse($detail->created_at)->format('d/m/Y H:i') }}</td> <!-- Muestra fecha y hora -->
                        <td>{{ number_format($detail->payment, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totales -->
        <div class="total">
            <p><strong>Total Pagado:</strong> <span>S/ {{ number_format($reservation_detail->sum('payment'), 2) }}</span></p>
            <p><strong>Saldo Pendiente:</strong> <span class="highlight">S/ {{ number_format($reservation->total - $reservation_detail->sum('payment'), 2) }}</span></p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Sistema ERP Deportivo</p>
        </div>
    </div>
</body>
</html>
