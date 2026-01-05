<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock mínimo pasado</title>
</head>
<body>
    <div>
        <table >
            <tr>
                <td style="width: 220px; font-weight: bold;">EMPRESA</td>
                <td style="font-size: 12px;">{{ $company->business_name  }}</td>
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
            REPORTE STOCK MINIMO
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
                <td style="width:160px;"><strong>ESTABLECIMIENTO:</strong></td>
                <td>{{ $filters->get('establecimiento') }}</td>
            </tr>
        </table>

        <!-- Tabla del reporte -->
        <table>
            <thead>
                <tr>
                    <th width="30" style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">PRODUCTO</th>
                    <th width="30" style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">CATEGORÍA</th>
                    <th width="15" style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">MARCA</th>
                    <th width="15" style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">STOCK</th>
                    <th width="15" style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">STOCK MIN</th>
                    <th width="15" style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">UNIDAD</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalValor = 0;
                @endphp

                @foreach($data as $dato)
                    <tr>
                        <td>{{ $dato->name }}</td>
                        <td>{{ $dato->category_name }}</td>
                        <td>{{ $dato->brand_name }}</td>
                        <td>{{ $dato->stock }}</td>
                        <td>{{ $dato->stock_min }}</td>
                        <td>{{ $dato->unit_measurement }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; {{ now()->year }} {{ $company->business_name  }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
