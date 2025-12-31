<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kardex Producto</title>
</head>

<body>
    <div>
        <table>
            <tr>
                <td style="width: 220px; font-weight: bold;">EMPRESA</td>
                <td style="font-size: 12px;">{{ $company->business_name }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">RUC</td>
                <td style="font-size: 10px;">{{ $company->ruc }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">DIRECCIÓN</td>
                <td style="font-size: 10px;">{{ $company->fiscal_address }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">TELÉFONO</td>
                <td style="font-size: 10px;">{{ $company->phone }}</td>
            </tr>
            <tr>
                <td style="width: 220px; font-weight: bold;">EMAIL</td>
                <td style="font-size: 10px;">{{ $company->email }}</td>
            </tr>
        </table>

        <div class="header-title">
            KARDEX PRODUCTO
        </div>

        <!-- Información adicional -->
        <table class="info-table">
            <tr>
                <td style="width:160px;"><strong>USUARIO IMPRESIÓN:</strong></td>
                <td>{{ Auth::user()->name }}</td>
            </tr>
            <tr>
                <td style="width:160px;"><strong>FECHA IMPRESIÓN:</strong></td>
                <td>{{ now()->format('Y-m-d H:i:s') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>FECHA INICIO:</strong></td>
                <td>{{ $filters->get('start_date') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>FECHA FIN:</strong></td>
                <td>{{ $filters->get('end_date') }}</td>
            </tr>
            <tr>
                <td class="label"><strong>PRODUCTO:</strong></td>
                <td>{{ $filters->get('product_name') }}</td>
            </tr>
        </table>

        <!-- Tabla del reporte -->
        <table>
            <thead>
                <tr>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Id
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Fecha
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Producto
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Categoría
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Marca
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Tipo
                    </th>
                    <th class="minw-100px"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Doc
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Stock
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Entrada
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Salida
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Saldo
                    </th>
                    <th width="30"
                        style="background:#1B587C;color:white;text-align:center;border:1px solid #4EA4D8;text-transform:uppercase">
                        Registrador
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $item)
                    <tr>
                        <td>{{ $item->product_id }}</td>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->product_name }}</td>
                        <td>{{ $item->category_name }}</td>
                        <td>{{ $item->brand_name }}</td>
                        <td>{{ $item->type }}</td>
                        <td>{{ $item->document_serie }}</td>
                        <td>{{ number_format($item->previous_stock, 2) }}</td>
                        <td>{{ number_format($item->entrada, 2) }}</td>
                        <td>{{ number_format($item->salida, 2) }}</td>
                        <td>{{ number_format($item->later_stock, 2) }}</td>
                        <td>{{ $item->creator_user_name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ now()->year }} {{ $company->razon_social }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>

</html>
