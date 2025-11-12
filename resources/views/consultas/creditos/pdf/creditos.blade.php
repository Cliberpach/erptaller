<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Créditos Pendientes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 30px;
        }

        .header {
            margin-bottom: 20px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }

        .company-info h2 {
            margin: 0;
            color: #3a6ea5;
        }

        .company-info p {
            margin: 2px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: center;
        }

        th {
            background-color: #f2f2f2;
        }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #888;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <!-- DATOS DE LA EMPRESA -->
    <div class="header">
        <div class="company-info">
            <h2>{{ $company->business_name }}</h2>
            <p>RUC: {{ $company->ruc }}</p>
            <p>Dirección: {{ $company->fiscal_address }}</p>
            <p>Teléfono: {{ $company->phone }}</p>
            <p>Email: {{ $company->email }}</p>
            <p><strong>Fecha de Reporte:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}</p>
        </div>
    </div>

    <!-- TABLA DE CRÉDITOS -->
    @php
        if($search_estado === 'PAGADO'){
            $descripcion_estado = 'FACTURADOS';
        }else{
            $descripcion_estado = 'PENDIENTES';
        }
    @endphp


    <h3>CRÉDITOS {{ strtoupper($descripcion_estado) }}</h3>


    <table>
        <thead>
            <tr>
                <th>Cliente</th>
                <th>DNI / RUC</th>
                <th>Teléfono</th>
                <th>Campo</th>
                <th>Horario</th>
                <th>Fecha</th>
                <th>Horas</th>
                <th>Pelota</th>
                <th>Chaleco</th>
                <th>DNI</th>
                <th>Monto (S/)</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($credits as $credit)
                <tr>
                    <td>{{ $search_type === 'ruc' ? $credit->razon_social : $credit->customer_name }}</td>
                    <td>{{ $search_type === 'ruc' ? $credit->ruc_number : $credit->customer_document_number }}</td>
                    <td>{{ $credit->customer_phone }}</td>
                    <td>{{ $credit->field_name }}</td>
                    <td>{{ $credit->start_time }} - {{ $credit->end_time }}</td>
                    <td>{{ \Carbon\Carbon::parse($credit->date)->format('d/m/Y') }}</td>
                    <td>{{ $credit->total_hours }}</td>
                    <td>{{ $credit->ball ? 'Sí' : 'No' }}</td>
                    <td>{{ $credit->vest ? 'Sí' : 'No' }}</td>
                    <td>{{ $credit->dni ? 'Sí' : 'No' }}</td>
                    <td>S/ {{ number_format($credit->amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11">No hay créditos pendientes registrados en este rango de fechas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- PIE DE PÁGINA -->
    <div class="footer">
        &copy; {{ date('Y') }} {{ $company->business_name }} - Todos los derechos reservados.
    </div>
</body>
</html>
