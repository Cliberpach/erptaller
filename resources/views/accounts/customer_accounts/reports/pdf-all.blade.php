<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte cuentas cliente</title>
    <link rel="icon" href="{{ asset('img/gas.ico') }}" type="image/x-icon">
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
            font-size: 12px;
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
            font-size: 10px;
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
            font-size: 10px;
        }

        .tbl-report-sale td {
            padding: 6px;
            border: 1px solid #ccc;
            font-size: 10px;
        }

        /*======== FOOTER ==========*/
        @page {
            margin: 30px 50px 90px 50px;
        }

        header {
            position: fixed;
            top: -80px;
            left: 0;
            right: 0;
            height: 80px;
        }

        footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-size: 10px;
            color: #777;
        }

        .footer-content {
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Encabezado con logo e información de la empresa -->
        <table class="header-table">
            <tr>
                <!-- COLUMNA 1: LOGO -->
                @if ($company->logo_url)
                    <td style="width: 20%; text-align: left; vertical-align: top;">
                        <img src="{{ public_path($company->logo_url) }}" alt="Logo"
                            style="height: 100px; object-fit: contain; max-width: 120px;">
                    </td>
                @else
                    <td style="width: 20%; text-align: left; vertical-align: top;">
                        <img src="{{ public_path('assets/images/tu_logo.jpg') }}" alt="Logo"
                            style="height: 100px; object-fit: contain; max-width: 120px;">
                    </td>
                @endif

                <!-- Columna 2: Información de la empresa -->
                <td style="width: 80%; text-align: left;">
                    <h2 style="margin: 0; font-size: 14px; color: #3a6ea5;">{{ $company->business_name }}</h2>
                    <p style="margin: 0; font-size: 14px; color: #555;">RUC: {{ $company->ruc }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">{{ $company->fiscal_address }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Teléfono: {{ $company->phone }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Email: {{ $company->email }}</p>
                </td>


            </tr>
        </table>

        <div style="text-align: right; font-size: 12px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">
            CUENTAS PROVEEDOR
        </div>

        <!-- Segunda tabla: Información adicional -->
        <table class="info-table-custom">

            <tr>
                <td class="label">CLIENTE:</td>
                <td>{{ $filters->customer?->name }}</td>
            </tr>

            <tr>
                <td class="label">FECHA INICIO:</td>
                <td>{{ $filters->start_date }}</td>
            </tr>

            <tr>
                <td class="label">FECHA FIN:</td>
                <td>{{ $filters->end_date }}</td>
            </tr>

            <tr>
                <td class="label">ESTADO:</td>
                <td>{{ $filters->status }}</td>
            </tr>

            <tr>
                <td class="label">USUARIO IMPRESIÓN:</td>
                <td>{{ Auth::user()->name }}</td>
            </tr>
            <tr>
                <td class="label">FECHA IMPRESIÓN:</td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
            </tr>
        </table>

        <!-- Tercera tabla: Reporte Inventario -->
        <table class="tbl-report-sale">
            <thead>
                <tr>
                    <th class="text-center">CLIENTE</th>
                    <th class="text-center">DOC</th>
                    <th class="text-center">REG</th>
                    <th class="text-center">FEC REG</th>
                    <th class="text-center">MONTO</th>
                    <th class="text-center">ACTA</th>
                    <th class="text-center">SALDO</th>
                    <th class="text-center">ESTADO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $item->customer_name }}</td>
                        <td>{{ $item->document_number }}</td>
                        <td>{{ $item->creator_user_name }}</td>
                        <td>{{ $item->document_date }}</td>
                        <td>{{ number_format($item->amount, 2, '.', ',') }}</td>
                        <td>{{ number_format($item->paid_amount, 2, '.', ',') }}</td>
                        <td>{{ number_format($item->balance, 2, '.', ',') }}</td>
                        <td>{{ $item->status }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        <!-- Footer -->
        <footer>
            <div class="footer-content">
                <p>&copy; {{ now()->year }} {{ $company->business_name }} - Todos los derechos reservados</p>
            </div>
        </footer>

    </div>
</body>

</html>
