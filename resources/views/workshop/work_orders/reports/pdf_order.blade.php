<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" href="{{ asset('img/gas.ico') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ORDEN DE TRABAJO</title>
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

    <style>
        .img-table {
            width: 100%;
            border-collapse: collapse;
        }

        .img-table td {
            text-align: center;
            padding: 4px;
            border: 1px solid #d5cece;
            background: #fafafa;
        }

        .img-cell-img {
            width: 190px;
            height: auto;
            object-fit: contain;
            border: 1px solid #bbb;
            padding: 3px;
            border-radius: 4px;
        }

        .img-name {
            margin-top: 6px;
            font-size: 11px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="container">

        <!-- Encabezado con logo e información de la company -->
        <table class="header-table">
            <tr>
                <!-- Columna 1: Imagen -->
                <td style="width: 20%; text-align: left;">
                    <img src="{{ $company->logo_ruta }}" alt="Logo"
                        style="height: 100px; object-fit: contain; max-width: 120px;">
                </td>

                <!-- Columna 2: Información de la company -->
                <td style="width: 80%; text-align: left;">
                    <h2 style="margin: 0; font-size: 14px; color: #3a6ea5;">{{ $company->business_name }}</h2>
                    <p style="margin: 0; font-size: 14px; color: #555;">RUC: {{ $company->ruc }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">{{ $company->fiscal_address }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Teléfono: {{ $company->phone }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">EMAIL: {{ $company->email }}</p>
                </td>
            </tr>
        </table>

        <div style="text-align: right; font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">
            ORDEN DE TRABAJO N°{{ $data_order['order']->id }}
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

        </table>

        <!-- DATOS DE LA ORDEN -->
        <table class="info-table-custom" style="margin-top: 20px;">

            <tr>
                <td class="label">FECHA REGISTRO:</td>
                <td>{{ $data_order['order']->created_at }}</td>
            </tr>

            <tr>
                <td class="label">CLIENTE:</td>
                <td>{{ $data_order['order']->customer_name }}</td>
            </tr>

            <tr>
                <td class="label">DOCUMENTO:</td>
                <td>{{ $data_order['order']->customer_type_document_abbreviation }} -
                    {{ $data_order['order']->customer_document_number }}</td>
            </tr>

            <tr>
                <td class="label">PLACA:</td>
                <td>{{ $data_order['order']->plate }}</td>
            </tr>

            <tr>
                <td class="label">USUARIO CREACIÓN:</td>
                <td>{{ $data_order['order']->create_user_name }}</td>
            </tr>

            <tr>
                <td class="label">ESTADO:</td>
                <td>{{ $data_order['order']->status }}</td>
            </tr>

            <tr>
                <td class="label" style="font-weight:bold; padding-top: 10px;">SUBTOTAL:</td>
                <td style="text-align: right; font-weight:bold;">
                    {{ number_format(round($data_order['order']->subtotal, 2), 2, '.', ',') }}
                </td>
            </tr>

            <tr>
                <td class="label" style="font-weight:bold;">IGV (18%):</td>
                <td style="text-align: right; font-weight:bold;">
                    {{ number_format(round($data_order['order']->igv, 2), 2, '.', ',') }}
                </td>
            </tr>

            <tr>
                <td class="label" style="font-weight:bold;">TOTAL:</td>
                <td style="text-align: right; font-weight:bold;">
                    {{ number_format(round($data_order['order']->total, 2), 2, '.', ',') }}
                </td>
            </tr>
        </table>


        <!-- Tercera tabla: Reporte Productos -->
        <table class="tbl-report-sale">
            <thead>
                <tr>
                    <th>PRODUCTO</th>
                    <th>CATEGORÍA</th>
                    <th>MARCA</th>
                    <th>CANT</th>
                    <th>PREC VENTA</th>
                    <th>MONTO</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data_order['products'] as $item)
                    <tr>

                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->category_name }}</td>
                        <td>{{ $item->brand_name }}</td>

                        <!-- Cantidad con 2 decimales -->
                        <td style="text-align:right;">
                            {{ number_format(round($item->quantity, 2), 2, '.', ',') }}
                        </td>

                        <!-- Precio venta con formato -->
                        <td style="text-align:right;">
                            {{ number_format(round($item->price_sale, 2), 2, '.', ',') }}
                        </td>

                        <!-- Monto total -->
                        <td style="text-align:right;">
                            {{ number_format(round($item->amount, 2), 2, '.', ',') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Cuarta tabla: Reporte Servicios -->
        <table class="tbl-report-sale">
            <thead>
                <tr>
                    <th>SERVICIO</th>
                    <th>CANT</th>
                    <th>PREC VENTA</th>
                    <th>MONTO</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data_order['services'] as $srv)
                    <tr>

                        <!-- Nombre del servicio -->
                        <td>{{ $srv->service_name }}</td>

                        <!-- Cantidad -->
                        <td style="text-align:right;">
                            {{ number_format(round($srv->quantity, 2), 2, '.', ',') }}
                        </td>

                        <!-- Precio venta -->
                        <td style="text-align:right;">
                            {{ number_format(round($srv->price_sale, 2), 2, '.', ',') }}
                        </td>

                        <!-- Importe -->
                        <td style="text-align:right;">
                            {{ number_format(round($srv->amount, 2), 2, '.', ',') }}
                        </td>

                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Quinta tabla: Reporte Inventario -->
        <table class="tbl-report-sale">
            <thead>
                <tr>
                    <th>INVENTARIO</th>
                </tr>
            </thead>

            <tbody>
                @foreach ($data_order['inventory'] as $inv)
                    <tr>
                        <td>{{ $inv->inventory_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Sexta tabla: Reporte Técnicos -->
        <table class="tbl-report-sale">
            <thead>
                <tr>
                    <th>TÉCNICOS</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data_order['technicians'] as $tec)
                    <tr>
                        <td>{{ $tec->technical_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>


        <div style="page-break-inside: avoid;">

            <table class="img-table">
                <thead>
                    <tr>
                        <th colspan="3"
                            style="
                            text-align: left;
                            font-size: 10px;
                            padding: 4px;
                            background: #f2f2f2;
                            text-transform: uppercase;
                            letter-spacing: 0.5px;
                            font-family: DejaVu Sans, sans-serif;
                        ">
                            Imágenes
                        </th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($data_order['images']->chunk(3) as $row)
                        <tr>
                            @foreach ($row as $img)
                                <td style="text-align:center; padding:6px;">
                                    <img src="{{ public_path($img->img_route) }}" alt="{{ $img->img_name }}"
                                        style="width: 160px; height:auto; object-fit: contain;">

                                    <div style="font-size: 9px; margin-top: 3px;">
                                        {{ $img->img_name }}
                                    </div>
                                </td>
                            @endforeach

                            @for ($i = $row->count(); $i < 3; $i++)
                                <td></td>
                            @endfor
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>



        <!-- Footer -->
        <footer>
            <div class="footer-content">
                <p>&copy; {{ now()->year }} {{ $company->business_name }} - Todos los derechos reservados</p>
            </div>
        </footer>
    </div>
</body>

</html>
