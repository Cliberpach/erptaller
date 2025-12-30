<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Vehículos</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            height: 100%;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 100%;
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .details p,
        .totals p {
            margin: 5px 0;
            font-size: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        td {
            padding: 6px;
            vertical-align: top;
        }

        .header-table td {
            border: none;
        }

        .info-table-custom {
            margin-top: 20px;
            width: 100%;
        }

        .info-table-custom td {
            font-size: 12px;
            border: 1px solid #d4f1ff;
        }

        .info-table-custom .label {
            font-weight: bold;
            background-color: #f5f5f5;
        }



        .tbl-report-sale {
            margin-top: 20px;
            width: 100%;
            border: 1px solid #ccc;
        }

        .tbl-report-sale th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: left;
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 12px;
        }

        .tbl-report-sale td {
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 12px;
        }


        .footer {
            text-align: center;
            margin-top: 30px;
            padding: 15px 0;
            background-color: #f8f9fa;
            color: #6c757d;
            font-size: 12px;
            border-top: 1px solid #e9ecef;
        }

        .footer p {
            margin: 0;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Encabezado con logo e información de la empresa -->
        <table class="header-table">
            <tr>
                <!-- Columna 1: Imagen -->
                <td style="width: 20%; text-align: left;">
                    <img src="{{ $company->logo_ruta }}" alt="Logo"
                        style="height: 100px; object-fit: contain; max-width: 120px;">
                </td>

                <!-- Columna 2: Información de la empresa -->
                <td style="width: 80%; text-align: left;">
                    <h2 style="margin: 0; font-size: 14px; color: #3a6ea5;">{{ $company->business_name }}</h2>
                    <p style="margin: 0; font-size: 14px; color: #555;">RUC: {{ $company->ruc }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">{{ $company->direccion }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Teléfono: {{ $company->telefono }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">EMAIL: {{ $company->correo }}</p>
                </td>
            </tr>
        </table>

        <div style="text-align: right; font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">
            REPORTE VEHÍCULOS
        </div>

        <!-- Segunda tabla: Información adicional -->
        <table class="info-table-custom">
            <tr>
                <td class="label">USUARIO IMPRESIÓN:</td>
                <td>{{ Auth::user()->name }}</td>
            </tr>
            <tr>
                <td class="label">FECHA IMPRESIÓN:</td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label">FECHA INICIO:</td>
                <td>{{ $filters->get('start_date') }}</td>
            </tr>
            <tr>
                <td class="label">FECHA FIN:</td>
                <td>{{ $filters->get('end_date') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>CLIENTE:</strong></td>
                <td>{{ $filters->get('customer_name') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>VEHÍCULO:</strong></td>
                <td>{{ $filters->get('plate') }}</td>
            </tr>
            {{-- <tr>
                <td style="background-color: yellow; font-weight: bold;" class="label"><strong>TOTAL UTILIDAD:</strong></td>
                <td style="background-color: yellow; font-weight: bold;">{{ number_format($resultado->utilidad_total, 3) }}</td>
            </tr> --}}


        </table>

        <!-- Tercera tabla: Reporte Kardex Valorizado -->
        <table class="tbl-report-sale">
            <thead>
                <tr>
                    <th>FECHA</th>
                    <th>DOC</th>
                    <th>CLIENTE</th>
                    <th>PLACA</th>
                    <th>CONDICIÓN</th>
                    <th>TOTAL</th>
                    <th>ACTA</th>
                    <th>SALDO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->document_number }}</td>
                        <td>{{ $item->customer_name }}</td>
                        <td>{{ $item->plate }}</td>
                        <td>{{ $item->payment_condition }}</td>
                        <td>{{ number_format($item->amount, 2, '.', ',') }}</td>
                        <td>{{ number_format($item->paid, 2, '.', ',') }}</td>
                        <td>{{ number_format($item->balance, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer" style="vertical-align:bottom;">
            <p>&copy; {{ now()->year }} {{ $company->razon_social }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>

</html>
