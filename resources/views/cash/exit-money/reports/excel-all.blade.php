<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Egresos</title>
</head>

<body>
    <div>
        {{-- Sin logo: el conversor HTML->xlsx de Maatwebsite (FromView) no embebe
             <img> base64 (lo trata como ruta de archivo y rompe el export). Encabezado
             solo texto, igual que el molde de CxC. --}}
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

        {{-- El título va dentro de una <table>: el conversor HTML->xlsx de Maatwebsite
             solo vuelca contenido de tablas (un <div> suelto se descarta). --}}
        <table>
            <tr>
                <td style="font-size:14px; font-weight:bold;">REPORTE DE EGRESOS</td>
            </tr>
        </table>

        <!-- Información adicional + filtros activos -->
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
                <td style="width:160px;"><strong>PROVEEDOR :</strong></td>
                <td>{{ $filters->supplier?->name ?? 'TODOS' }}</td>
            </tr>
            <tr>
                <td style="width:160px;"><strong>RAZÓN :</strong></td>
                <td>{{ $filters->reason ?: 'TODAS' }}</td>
            </tr>
            <tr>
                <td style="width:160px;"><strong>PERÍODO :</strong></td>
                <td>{{ $filters->from_date ?? '-' }} a {{ $filters->to_date ?? '-' }}</td>
            </tr>
        </table>

        <!-- Tabla del reporte -->
        <table>
            <thead>
                <tr>
                    <th width="20"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        FECHA EMISIÓN</th>
                    <th width="20"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        RAZÓN</th>
                    <th width="40"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        PROVEEDOR</th>
                    <th width="20"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        NÚMERO</th>
                    <th width="15"
                        style="background:#1B587C;color:white;text-align: center;border:1px solid #4EA4D8;text-transform: uppercase">
                        TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach ($data as $item)
                    @php $total += (float) $item->total; @endphp
                    <tr>
                        <td>{{ $item->date }}</td>
                        <td>{{ $item->reason }}</td>
                        <td>{{ $item->supplier_name }}</td>
                        <td>{{ $item->number }}</td>
                        <td style="text-align:right;">{{ number_format($item->total, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="text-align:right; font-weight:bold; background:#e0e0e0;">TOTAL</td>
                    <td style="text-align:right; font-weight:bold; background:#e0e0e0;">
                        {{ number_format($total, 2, '.', ',') }}
                    </td>
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
