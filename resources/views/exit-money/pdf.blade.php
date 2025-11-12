<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Egreso #{{ $exit_money->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        h3 {
            color: #4CAF50;
        }
        p {
            margin: 4px 0;
        }
        .container {
            border: 1px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
            font-weight: bold;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        td:nth-child(3), th:nth-child(3) {
            text-align: right;
        }
        .total-row {
            background-color: #f4f4f4;
        }
        .summary p {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <h3>Comprobante de Egreso #{{ $exit_money->id }}</h3>
        <p><strong>Empresa:</strong> {{ $company->business_name }}</p>
        <p><strong>RUC:</strong> {{ $company->ruc }}</p>
        <p><strong>Fecha de emisión:</strong> {{ $exit_money->date }}</p>
        <p><strong>Razón del egreso:</strong> {{ $exit_money->reason }}</p>
        <p><strong>Proveedor:</strong> {{ $exit_money->supplier->name }}</p>
        <p><strong>Tipo de pago:</strong> {{ $exit_money->payment_type }}</p>
    </div>

    <h3>Detalles del Egreso</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Descripción</th>
                <th>Total (S/)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalDetalles = 0;
            @endphp
            @foreach ($exit_money_detail as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->description }}</td>
                    <td>{{ number_format($detail->total, 2) }}</td>
                </tr>
                @php
                    $totalDetalles += $detail->total;
                @endphp
            @endforeach
            <!-- Fila para mostrar el total acumulado de los detalles -->
            <tr class="total-row">
                <td colspan="2"><strong>Total de detalles</strong></td>
                <td><strong>{{ number_format($totalDetalles, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="summary">
        <p><strong>Total del egreso:</strong> S/ {{ number_format($exit_money->total, 2) }}</p>
    </div>

</body>
</html>
