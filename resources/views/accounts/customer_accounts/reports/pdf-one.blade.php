<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="{{ asset('img/gas.ico') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CUENTA CLIENTE</title>
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
            font-size: 12px;
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

        <!-- Encabezado con logo e información de la company -->
        <table class="header-table" style="width:100%; border-collapse: collapse;">
            <tr>

                <!-- COLUMNA 1: LOGO -->
                <td style="width: 20%; text-align: left; vertical-align: top;">
                    <img src="{{ public_path($company->logo_url) }}" alt="Logo"
                        style="height: 100px; object-fit: contain; max-width: 120px;">
                </td>

                <!-- COLUMNA 2: INFO DE LA EMPRESA -->
                <td style="width: 60%; text-align: left; vertical-align: top;">
                    <h2 style="margin: 0; font-size: 14px; color: #3a6ea5;">
                        {{ $company->business_name }}
                    </h2>
                    <p style="margin: 0; font-size: 12px; color: #555;">RUC: {{ $company->ruc }}</p>
                    <p style="margin: 0; font-size: 12px; color: #555;">{{ $company->fiscal_address }}</p>
                    <p style="margin: 0; font-size: 12px; color: #555;">Teléfono: {{ $company->phone }}</p>
                    <p style="margin: 0; font-size: 12px; color: #555;">Email: {{ $company->email }}</p>
                </td>

                <!-- COLUMNA 3: RECUADRO COTIZACIÓN -->
                <td style="width: 20%; text-align: center; vertical-align: top;">
                    <div
                        style="
                        border: 1px solid #000;
                        padding: 10px 5px;
                        font-size: 12px;
                        font-weight: bold;
                        display: inline-block;
                        width: 100%;
                    ">
                        CUENTA CLIENTE<br>
                        <span style="font-size: 14px;">
                            CC-{{ str_pad($cuenta->id, 8, '0', STR_PAD_LEFT) }}
                        </span>
                    </div>
                </td>

            </tr>
        </table>

        <!-- DATOS DE LA ORDEN -->
        <table class="info-table-custom" style="margin-top: 20px;">

            <tr>
                <td class="label">FECHA IMPRESIÓN:</td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label">FECHA REGISTRO:</td>
                <td>{{ $cuenta->created_at }}</td>
            </tr>

            <tr>
                <td class="label">CLIENTE:</td>
                <td>{{ $cliente->type_document_abbreviation . ':' . $cliente->document_number . '-' . $cliente->name }}
                </td>
            </tr>

            <tr>
                <td class="label">DOCUMENTO ORIGEN:</td>
                <td>{{ $documento }}</td>
            </tr>

        </table>

        @if ($detalle->count() > 0)
            <table class="tbl-report-sale">
                <thead class="table-primary text-center">
                    <tr>
                        <th>FECHA</th>
                        <th>OBS</th>
                        <th>MONTO</th>
                        <th>IMG</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($detalle as $item)
                        <tr>
                            <td class="text-center" style="width:20%;">
                                {{ \Carbon\Carbon::parse($item->date)->format('d/m/Y') }}
                            </td>

                            <td style="width:40%;">
                                {{ $item->observation }}
                            </td>

                            <td style="width:20%;">
                                {{ number_format($item->amount, 2, '.', ',') }}
                            </td>

                            <td style="width:20%;">
                                @if ($item->img_route)
                                    <img src="{{ public_path($item->img_route) }}"
                                        style="height: 60px;object-fit:contain;">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @php
            $pago = $cuenta->amount - $cuenta->balance;
        @endphp

        <table style="width: 100%; margin-top: 20px; font-size: 14px;">
            <tr>
                <td style="text-align: right; font-weight: bold;">TOTAL:</td>
                <td style="text-align: right; width: 120px;">
                    S/ {{ number_format($cuenta->amount, 2, '.', ',') }}
                </td>
            </tr>

            <tr>
                <td style="text-align: right; font-weight: bold;">PAGÓ:</td>
                <td style="text-align: right;">
                    S/ {{ number_format($pago, 2, '.', ',') }}
                </td>
            </tr>

            <tr>
                <td style="text-align: right; font-weight: bold;">SALDO:</td>
                <td style="text-align: right;">
                    S/ {{ number_format($cuenta->balance, 2, '.', ',') }}
                </td>
            </tr>
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
