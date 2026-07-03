<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Nota de Crédito</title>
    <style>
        body { font-size: 6pt; font-family: Arial, Helvetica, sans-serif; color: black; }
        .cabecera { align-content: center; text-align: center; }
        .logo { width: 100%; margin: 0; padding: 0; }
        .img-fluid { width: 60%; height: 70px; margin-bottom: 10px; }
        .empresa { position: relative; align-content: center; }
        .comprobante { width: 100%; }
        .numero-documento { margin: 1px; padding-top: 20px; padding-bottom: 20px; border: 1px solid #8f8f8f; }
        .informacion { width: 100%; position: relative; }
        .tbl-informacion { width: 100%; }
        .cuerpo { width: 100%; position: relative; margin-bottom: 10px; }
        .tbl-detalles { width: 100%; }
        .tbl-detalles thead { border-top: 1px solid; background-color: rgb(241, 239, 239); }
        .tbl-detalles tbody { border-top: 1px solid; border-bottom: 1px solid; }
        .box-afectado { width: 100%; border: 1px solid #8f8f8f; margin-bottom: 6px; }
        .box-afectado td { padding: 1px 3px; }
        .m-0 { margin: 0; } .p-0 { padding: 0; }
        .text-uppercase { text-transform: uppercase; }
    </style>
</head>

<body>
    <div class="cabecera">
        <div class="logo">
            @if ($company->logo_url)
                <img src="{{ $company->logo_url }}" class="img-fluid" style="object-fit: contain;">
            @endif
        </div>
        <div class="empresa">
            <p class="m-0 p-0 text-uppercase">{{ $company->abbreviated_business_name }}</p>
            <p class="m-0 p-0 text-uppercase">RUC {{ $company->ruc }}</p>
            <p class="m-0 p-0 text-uppercase">{{ $company->fiscal_address }}</p>
            <p class="m-0 p-0">Central telefónica: {{ $company->phone }}</p>
            <p class="m-0 p-0">Email: {{ $company->email }}</p>
        </div><br>
        <div class="comprobante">
            <div class="numero-documento">
                <p class="m-0 p-0 text-uppercase">{{ $credit_note->type_sale_name }}</p>
                <p class="m-0 p-0 text-uppercase">{{ $credit_note->serie . '-' . $credit_note->correlative }}</p>
            </div>
        </div>
    </div><br>

    <div class="informacion">
        <table class="tbl-informacion">
            <tr>
                <td>F. EMISIÓN</td><td>:</td>
                <td>{{ \Carbon\Carbon::parse($credit_note->fechaEmision ?? $credit_note->created_at)->format('d/m/Y') }}</td>
            </tr>
            <tr><td>CLIENTE</td><td>:</td><td class="text-uppercase">{{ $credit_note->customer_name }}</td></tr>
            <tr>
                <td class="text-uppercase">{{ $credit_note->customer_type_document }}</td><td>:</td>
                <td>{{ $credit_note->customer_document_number }}</td>
            </tr>
            <tr><td>ATENDIDO POR</td><td>:</td><td class="text-uppercase">{{ $credit_note->creator_user_name }}</td></tr>
        </table>
    </div><br>

    {{-- Documento afectado + motivo SUNAT --}}
    @php
        $afectado = $credit_note->tipDocAfectado === '01' ? 'FACTURA' : ($credit_note->tipDocAfectado === '03' ? 'BOLETA' : $credit_note->tipDocAfectado);
    @endphp
    <table class="box-afectado">
        <tr><td style="width:38%;"><b>DOC. AFECTADO</b></td><td>{{ $afectado }} {{ $credit_note->numDocfectado }}</td></tr>
        <tr><td><b>MOTIVO</b></td><td>{{ $credit_note->codMotivo }} - {{ $credit_note->desMotivo }}</td></tr>
        @if ($credit_note->obsevation)
            <tr><td><b>OBSERVACIÓN</b></td><td class="text-uppercase">{{ $credit_note->obsevation }}</td></tr>
        @endif
    </table>

    <div class="cuerpo">
        <table class="tbl-detalles text-uppercase" cellpadding="2" cellspacing="0">
            <thead>
                <tr>
                    <th style="text-align: left; width: 10%;">CANT</th>
                    <th style="text-align: left; width: 15%;">UM</th>
                    <th style="text-align: left; width: 55%;">DESCRIPCION</th>
                    <th style="text-align: left; width: 10%;">P.UNIT.</th>
                    <th style="text-align: right; width: 10%;">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($credit_note->details as $item)
                    <tr>
                        <td style="text-align: left">{{ number_format($item->quantity, 2) }}</td>
                        <td style="text-align: left">{{ $item->unity }}</td>
                        <td style="text-align: left">{{ $item->product_name . '-' . $item->brand_name }}</td>
                        <td style="text-align: left">{{ number_format($item->sale_price, 2) }}</td>
                        <td style="text-align: right">{{ number_format($item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" style="text-align:right">Sub Total: S/.</th>
                    <th style="text-align:right">{{ number_format($credit_note->subtotal, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:right">IGV: S/.</th>
                    <th style="text-align:right">{{ number_format($credit_note->igv_amount, 2) }}</th>
                </tr>
                <tr>
                    <th colspan="4" style="text-align:right">Total NC: S/.</th>
                    <th style="text-align:right">{{ number_format($credit_note->total, 2) }}</th>
                </tr>
            </tfoot>
        </table>
        <br>
        <p class="p-0 m-0 text-uppercase">SON: <b>{{ $credit_note->legend }}</b></p>
    </div>

    <div class="cabecera">
        @if ($credit_note->ruta_qr)
            <img style="height:120px;object-fit: contain;" src="{{ public_path($credit_note->ruta_qr) }}">
        @endif
    </div>
</body>

</html>
