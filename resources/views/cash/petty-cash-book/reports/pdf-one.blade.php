<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte Movimiento petty_cash_book</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            width: 90%;
            margin: 30px auto;
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .details p,
        .totals p {
            margin: 5px 0;
            font-size: 9px;
        }

        h3 {
            margin-top: 40px;
            color: #3a6ea5;
            text-align: left;
            font-size: 20px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }

        .table-info {
            width: 100%;
            table-layout: auto;
            /* Cambio aquí: 'auto' permite que las tablas ocupen más espacio */
            word-wrap: break-word;
            border-collapse: collapse;
            margin-bottom: 30px;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-info,
        .table-info th,
        .table-info td {
            border: 1px solid #ddd;
        }

        .table-info th {
            background-color: #f4f8fc;
            font-weight: bold;
            padding: 6px;
            font-size: 9px;
        }

        .table-info td {
            padding: 6px;
            text-align: center;
            font-size: 9px;
        }

        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 9px;
            color: #888;
        }

        .totals {
            background-color: #f4f8fc;
            padding: 6px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .totals p {
            font-size: 9px;
            font-weight: bold;
            margin: 10px 0;
        }


        /* TABLE TOTALES  */
        .table-info-totales {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .table-info-totales th,
        .table-info-totales td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
            font-size: 9px;
        }

        .table-info-totales th {
            background-color: #f4f8fc;
            font-weight: bold;
        }

        .table-info-totales td {
            font-size: 9px;
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

        <table style="width: 100%; border: 0; cellpadding: 0; cellspacing: 0;">
            <tr>
                <!-- Columna 1: Imagen -->
                <td style="width: 20%; text-align: left;">
                    <img src="{{ $company->logo_url }}" alt="Logo"
                        style="height: 100px;object-fit:contain;max-width:120px;">
                </td>

                <!-- Columna 2: Información de la empresa -->
                <td style="width: 60%; text-align: left;">
                    <h2 style="margin: 0; font-size: 17px; color: #3a6ea5;">{{ $company->business_name }}</h2>
                    <p style="margin: 0; font-size: 14px; color: #555;">RUC: {{ $company->ruc }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">{{ $company->fiscal_address }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">Teléfono: {{ $company->phone }}</p>
                    <p style="margin: 0; font-size: 14px; color: #555;">EMAIL: {{ $company->email }}</p>
                </td>

                <!-- Columna 3: Vacía -->
                <td style="width: 20%;"></td>
            </tr>
        </table>


        <table class="table-info" style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">CAJERO:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">{{ $cajero->name }}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">CAJA:
                </td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">
                    {{ $petty_cash_book->petty_cash_name }}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">TURNO:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">
                    {{ $petty_cash_book->shift->time }}
                </td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">MONTO INICIAL:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">
                    {{ $petty_cash_book->initial_amount }}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">FECHA APERTURA:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">
                    {{ \Carbon\Carbon::parse($petty_cash_book->initial_date)->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td style="width: 50%; padding: 2px; font-weight: bold; text-align: left; border: 1px solid #ddd;">FECHA CIERRE:</td>
                <td style="width: 50%; padding: 2px; text-align: left; border: 1px solid #ddd;">
                    {{ \Carbon\Carbon::parse($petty_cash_book->close_date)->format('d/m/Y H:i') }}</td>
            </tr>
        </table>


        <h5 style="font-size:9px;">VENTAS DEL DÍA</h5>

        <table class="table-info">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>CLIENTE</th>
                    <th>MONTO</th>
                    @foreach ($payment_methods as $payment_method)
                        <th>{{ $payment_method->description }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($sale_documents as $sale)
                    <tr>
                        <td>{{ $sale->serie . '-' . $sale->correlative }}</td>
                        <td>{{ $sale->customer_name }}</td>
                        <td style="text-align: right;">{{ number_format($sale->total, 2) }}</td>

                        @foreach ($payment_methods as $payment_method)
                            <td style="text-align: right;">
                                @if ($sale->method_pay_id_1 == $payment_method->id)
                                    {{ number_format($sale->amount_pay_1, 2) }}
                                @elseif ($sale->method_pay_id_2 == $payment_method->id)
                                    {{ number_format($sale->amount_pay_2, 2) }}
                                @else
                                    0.00
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight:bold; background:#e0e0e0;">
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td style="text-align: right;">
                        {{ number_format($consolidated['report_sales']['total'], 2, '.', ',') }}
                    </td>
                    @foreach ($consolidated['report_sales']['report'] as $item)
                        <td style="text-align: right;">{{ number_format($item['amount'], 2, '.', ',') }}</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>

        <h5 style="font-size:9px;">EGRESOS</h5>

        <table class="table-info table-sm table" style="border-collapse: collapse; width: 100%;">
            <thead style="background-color: #c0c0c0; color: #000;">
                <tr>
                    <th>N°</th>
                    <th>PROVEEDOR</th>
                    <th>DOC</th>
                    @foreach ($payment_methods as $payment_method)
                        <th>{{ $payment_method->description }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                @foreach ($exit_moneys as $index => $egreso)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $egreso->supplier->name }}</td>
                        <td>{{ $egreso->number }}</td>

                        @foreach ($payment_methods as $payment_method)
                            @php
                                $value = $egreso->payment_method_id == $payment_method->id ? $egreso->total : 0;
                            @endphp
                            <td style="text-align: right;">{{ number_format($value, 2, '.', ',') }}</td>
                        @endforeach
                    </tr>
                @endforeach

                <tr style="font-weight:bold; background:#e0e0e0;">
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td class="text-end">{{ number_format($consolidated['report_expenses']['total'], 2, '.', ',') }}
                    </td>
                    @foreach ($consolidated['report_expenses']['report'] as $item)
                        <td style="text-align: right;">{{ number_format($item['amount'], 2, '.', ',') }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>


        <h5 style="font-size:9px;">COBRANZA CLIENTE</h5>

        <table class="table-info table-sm table" style="border-collapse: collapse; width: 100%;">
            <thead style="background-color: #c0c0c0; color: #000;">
                <tr>
                    <th>CLIENTE</th>
                    <th>DOC</th>
                    <th>FECHA</th>
                    @foreach ($payment_methods as $payment_method)
                        <th>{{ $payment_method->description }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>

                @foreach ($customer_pays as $index => $pay)
                    <tr>
                        <td>{{ $pay->customer_name }}</td>
                        <td>{{ $pay->document_number }}</td>
                        <td>{{ $pay->created_at }}</td>

                        @foreach ($payment_methods as $payment_method)
                            @php
                                $value = 0;
                                if ($payment_method->id === 1) {
                                    $value += $pay->cash;
                                }

                                if ($payment_method->id !== 1 && $payment_method->id == $pay->payment_method_id) {
                                    $value += $pay->amount;
                                }
                            @endphp
                            <td style="text-align: right;">{{ number_format($value, 2, '.', ',') }}</td>
                        @endforeach
                    </tr>
                @endforeach

                <tr style="font-weight:bold; background:#e0e0e0;">
                    <td colspan="2" class="text-end">TOTAL</td>
                    <td class="text-end">
                        {{ number_format($consolidated['report_customer_accounts']['total'], 2, '.', ',') }}
                    </td>
                    @foreach ($consolidated['report_customer_accounts']['report'] as $item)
                        <td style="text-align: right;">{{ number_format($item['amount'], 2, '.', ',') }}</td>
                    @endforeach
                </tr>
            </tbody>
        </table>


        <h5 style="font-size:10px; margin-top:25px;">RESUMEN POR MÉTODO DE PAGO</h5>

        <table class="table-info" style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background:#f4f8fc;">
                    <th>MÉTODO</th>
                    <th style="text-align:right;">VENTAS</th>
                    <th style="text-align:right;">EGRESOS</th>
                    <th style="text-align:right;">COBRANZAS</th>
                    <th style="text-align:right;">TOTAL NETO</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($payment_methods as $pm)
                    @php
                        $sale = collect($consolidated['report_sales']['report'])->firstWhere(
                            'payment_method_id',
                            $pm->id,
                        );
                        $expense = collect($consolidated['report_expenses']['report'])->firstWhere(
                            'payment_method_id',
                            $pm->id,
                        );
                        $customer = collect($consolidated['report_customer_accounts']['report'])->firstWhere(
                            'payment_method_id',
                            $pm->id,
                        );

                        $sale_amount = $sale['amount'] ?? 0;
                        $expense_amount = $expense['amount'] ?? 0;
                        $customer_amount = $customer['amount'] ?? 0;

                        $net = $sale_amount - $expense_amount + $customer_amount;
                    @endphp

                    <tr>
                        <td>{{ $pm->description }}</td>
                        <td style="text-align:right;">{{ number_format($sale_amount, 2, '.', ',') }}</td>
                        <td style="text-align:right;">{{ number_format($expense_amount, 2, '.', ',') }}</td>
                        <td style="text-align:right;">{{ number_format($customer_amount, 2, '.', ',') }}</td>
                        <td style="text-align:right; font-weight:bold;">{{ number_format($net, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h5 style="font-size:10px; margin-top:25px;">RESUMEN FINAL</h5>

        <table class="table-info table-sm table" style="border-collapse: collapse; width: 100%;">
            <tr>
                <th>SALDO INICIAL</th>
                <td style="text-align:right;">
                    {{ number_format($consolidated['petty_cash_book']->initial_amount, 2, '.', ',') }}
                </td>
            </tr>
            <tr>
                <th> TOTAL VENTAS</th>
                <td style="text-align:right;">
                    {{ number_format($consolidated['report_sales']['total'], 2, '.', ',') }}
                </td>
            </tr>
            <tr>
                <th> TOTAL EGRESOS</th>
                <td style="text-align:right;">
                    {{ number_format($consolidated['report_expenses']['total'], 2, '.', ',') }}
                </td>
            </tr>
            <tr>
                <th> TOTAL COBRANZA CLIENTES</th>
                <td style="text-align:right;">
                    {{ number_format($consolidated['report_customer_accounts']['total'], 2, '.', ',') }}
                </td>
            </tr>
            <tr style="background:#e0f7fa;">
                <th> MONTO CIERRE</th>
                <td style="text-align:right; font-weight:bold;">
                    {{ number_format($consolidated['amount_close'], 2, '.', ',') }}
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
