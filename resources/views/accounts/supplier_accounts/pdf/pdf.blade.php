<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Cuenta Proveedor</title>
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

        .details p, .totals p {
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
                    <img src="{{ $empresa->logo_ruta }}" alt="Logo" style="height: 100px; object-fit: contain; max-width: 120px;">
                </td>

                <!-- Columna 2: Información de la empresa -->
                <td style="width: 80%; text-align: left;">
                    <h2 style="margin: 0; font-size: 14px; color: #3a6ea5;">{{ $empresa->razon_social }}</h2>
                    <p style="margin: 0; font-size: 14px; color: #555;">RUC: {{ $empresa->ruc }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">{{ $empresa->direccion }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Teléfono: {{ $empresa->telefono }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">EMAIL: {{ $empresa->correo }}</p>
                </td>

              
            </tr>
        </table>

        <div style="text-align: right; font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px;">
            CUENTA PROVEEDOR
        </div>

        <!-- Segunda tabla: Información adicional -->
        <table class="info-table-custom">
            <tr>
                <td class="label">DOCUMENTO:</td>
                <td>{{ $datos->cobranza->documento }}</td>
            </tr>
            <tr>
                <td class="label">PROVEEDOR:</td>
                <td>{{ $datos->cobranza->proveedor_nombre }}</td>
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
                    <th class="text-center">Fec Pago</th>
                    <th class="text-center">Caja</th>
                    <th class="text-center">Registrador</th>
                    <th class="text-center">Monto</th>
                    <th class="text-center">Efectivo</th>
                    <th class="text-center">Importe</th>
                    <th class="text-center">Saldo</th>
                    <th class="text-center">Mét Pago</th>
                    <th class="text-center">Observacion</th>
                    <th class="text-center">Fec Reg</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datos->detalle as $pago)
                <tr>
                    <td>{{ $pago->fecha }}</td>
                    <td>{{ $pago->caja_nombre }}</td>
                    <td>{{ $pago->registrador_nombre }}</td>
                    <td>{{ number_format($pago->monto, 2) }}</td>
                    <td>{{ number_format($pago->efectivo, 2) }}</td> 
                    <td>{{ number_format($pago->importe, 2) }}</td> 
                    <td>{{ number_format($pago->saldo, 2) }}</td> 
                    <td>{{ $pago->metodo_pago_nombre }}</td>
                    <td>{{ $pago->observacion }}</td>
                    <td>{{ $pago->created_at }}</td>
                </tr>
                @endforeach
            </tbody>
        </table> 

        <table style="width: 200px; margin-left: auto; border-collapse: collapse; font-size: 12px; border: 1px solid black;">
            <tr style="background-color: #f2f2f2;">
                <td style="text-align: right; font-weight: bold; padding: 6px; border-right: 1px solid black; border-bottom: 1px solid black;">
                    TOTAL CUENTA:
                </td>
                <td style="text-align: right; padding: 6px; border-bottom: 1px solid black;">
                    {{ number_format($datos->cobranza->monto, 2) }}
                </td>
            </tr>
            <tr>
                <td style="text-align: right; font-weight: bold; padding: 6px; border-right: 1px solid black;">
                    SALDO:
                </td>
                <td style="text-align: right; padding: 6px;">
                    {{ number_format($datos->cobranza->saldo, 2) }}
                </td>
            </tr>
        </table>
        
        
        <!-- Footer -->
        <div class="footer" style="vertical-align:bottom;">
            <p>&copy; {{ now()->year }} {{ $empresa->razon_social }} - Todos los derechos reservados</p>
        </div>
    </div>
</body>
</html>
