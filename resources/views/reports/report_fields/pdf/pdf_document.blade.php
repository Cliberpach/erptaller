<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ $sale_document->serie.'-'.$sale_document->correlative }}</title>
        <link rel="icon" href="{{ base_path() . '/img/siscom.ico' }}" />
        <style>
            body{
                font-size: 6pt;
                font-family: Arial, Helvetica, sans-serif;
                color: black;
            }

            .cabecera {
                align-content: center;
                text-align: center;
            }

            .logo{
                width: 100%;
                margin: 0px;
                padding: 0px;
            }

            .img-fluid {
                width: 60%;
                height: 70px;
                margin-bottom: 10px;
            }

            .empresa {
                position: relative;
                align-content: center;
            }

            .comprobante {
                width: 100%;
            }

            .numero-documento {
                margin: 1px;
                padding-top: 20px;
                padding-bottom: 20px;
                border: 1px solid #8f8f8f;
            }

            .informacion{
                width: 100%;
                position: relative;
            }

            .tbl-informacion {
                width: 100%;
            }

            .cuerpo{
                width: 100%;
                position: relative;
                margin-bottom: 10px;
            }

            .tbl-detalles {
                width: 100%;
            }

            .tbl-detalles thead{
                border-top: 1px solid;
                background-color: rgb(241, 239, 239);
            }

            .tbl-detalles tbody{
                border-top: 1px solid;
                border-bottom: 1px solid;
            }
            .tbl-detalles tfoot{
                font-size: 9.5px;
            }

            .tbl-qr {
                width: 100%;
            }

            .qr {
                position: relative;
                width: 100%;
                align-content: center;
                text-align: center;
                margin-top: 10px;
            }

            .tbl-info-credito {
                width: 100%;
                font-size: 6px;
                border: 1px solid black;
            }

            .tbl-info-retencion {
                width: 100%;
                font-size: 6px;
                border: 1px solid black;
            }
            /*---------------------------------------------*/

            .m-0{
                margin:0;
            }

            .text-uppercase {
                text-transform: uppercase;
            }

            .p-0{
                padding:0;
            }

            footer {
                color: #777777;
                width: 100%;
                height: 30px;
                position: absolute;
                bottom: 0;
                border-top: 1px solid #AAAAAA;
                padding: 8px 0;
                text-align: center;
            }
        </style>
    </head>

    <body>
        <div class="cabecera">
            <div class="logo">
                @if($company->logo_url)
                    <img src="{{ $company->logo_url }}" class="img-fluid" style="object-fit: contain;">
                @else
                    <img src="{{ public_path() . '/assets/img/img_default.png' }}" class="img-fluid" style="object-fit: contain;">
                @endif 
            </div>
            <div class="empresa">
                <p class="m-0 p-0 text-uppercase nombre-empresa">{{$company->abbreviated_business_name}}</p>
                <p class="m-0 p-0 text-uppercase ruc-empresa">RUC {{$company->ruc}}</p>
                <p class="m-0 p-0 text-uppercase direccion-empresa">{{$company->fiscal_address}}</p>

                <p class="m-0 p-0 text-info-empresa">Central telefónica: {{$company->phone}}</p>
                <p class="m-0 p-0 text-info-empresa">Email: {{$company->email}}</p> 
            </div><br>
            <div class="comprobante">
                <div class="numero-documento">
                    <p class="m-0 p-0 text-uppercase">{{ $sale_document->type_sale_name }}</p>
                    <p class="m-0 p-0 text-uppercase">{{$sale_document->serie.'-'.$sale_document->correlative}}</p> 
                </div>
            </div>
        </div><br>
        <div class="informacion">
            <table class="tbl-informacion">
                <tr>
                    <td>F. EMISIÓN</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($sale_document->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>F. VENC.</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($sale_document->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>CLIENTE</td>
                    <td>:</td>
                    <td>{{ $customer->name }}</td>
                </tr>
                <tr>
                    <td class="text-uppercase">{{ $customer->type_document_abbreviation}}</td>
                    <td>:</td>
                    <td>{{ $customer->document_number }}</td>
                </tr>
                <tr>
                    <td>DIRECCIÓN</td>
                    <td>:</td>
                    <td>{{ $customer->address }}</td>
                </tr>
                <tr>
                    <td>TELÉFONO</td>
                    <td>:</td>
                    <td class="text-uppercase">{{ $customer->phone}}</td>
                </tr>
                @if ($sale_document->observacion)
                <tr>
                    <td>OBSERVACIÓN</td>
                    <td>:</td>
                    <td class="text-uppercase">'-'</td>
                </tr>
                @endif
                <tr>
                    <td>ATENDIDO POR</td>
                    <td>:</td>
                    <td class="text-uppercase">{{$sale_document->user_recorder_name}}</td>
                </tr>
            </table>
        </div>
        <br>
        <div class="cuerpo">
            <table class="tbl-detalles text-uppercase" cellpadding="2" cellspacing="0">
                <thead>
                    <tr >
                        <th style="text-align: left; width: 10%;">CANT</th>
                        <th style="text-align: left; width: 15%;">UM</th>
                        <th style="text-align: left; width: 55%;">DESCRIPCION</th>
                        <th style="text-align: left; width: 10%;">P.UNIT.</th>
                        <th style="text-align: right; width: 10%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sale_document_detail as $item)

                        <tr>
                            <td style="text-align: left">{{ number_format($item->quantity, 2) }}</td>
                            <td style="text-align: left">{{ $item->product_unit }}</td>
                            <td style="text-align: left">{{ $item->product_name}}</td>
                            <td style="text-align: left">{{ number_format($item->price_sale,2) }}</td>
                            <td style="text-align: right">{{ number_format(($item->quantity) * $item->price_sale, 2) }}</td>
                        </tr>
                     
                    @endforeach
                   
                   
                </tbody>
                <tfoot>
                    @if($sale_document->type_sale_code != 80)
                        <tr>
                            <th colspan="4" style="text-align:right">Sub Total: S/.</th>
                            <th style="text-align:right">{{ number_format($sale_document->subtotal, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="text-align:right">IGV: S/.</th>
                            <th style="text-align:right">{{ number_format($sale_document->igv_amount, 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="text-align:right">Total a pagar: S/.</th>
                            <th style="text-align:right">{{ number_format($sale_document->total, 2) }}</th>
                        </tr>
                    @else
                        <tr>
                            <th colspan="4" style="text-align:right">Total a pagar: S/.</th>
                            <th style="text-align:right">{{ number_format($sale_document->total, 2) }}</th>
                        </tr>
                   @endif
                </tfoot> 
            </table> 
            <br>
            <p class="p-0 m-0 text-uppercase text-cuerpo">SON: <b>{{ $sale_document->legend }}</b></p> 
            <br>
            {{-- @if ($mostrar_cuentas === "SI")
                <table class="tbl-qr">
                    <tr>
                        <td>
                            @foreach($empresa->bancos as $banco)
                                <p class="m-0 p-0 text-cuerpo"><b class="text-uppercase">{{ $banco->descripcion}}</b> {{ $banco->tipo_moneda}} <b>N°: </b> {{ $banco->num_cuenta}} <b>CCI:</b> {{ $banco->cci}}</p>
                            @endforeach
                        </td>
                    </tr>
                </table>
            @endif --}}
            {{-- @if (strtoupper($documento->condicion->descripcion) == 'CREDITO' || strtoupper($documento->condicion->descripcion) == 'CRÉDITO')
                <br>
                <div style="border: 1px solid black; padding: 2px">
                    <table class="tbl-info-credito" style="margin-bottom: 2px;">
                        <tr>
                            <th colspan="3" style="text-align: left">Informacion del crédito</th>
                        </tr>
                        <tr>
                            <td style="text-align: left">Monto neto pendiente de pago</td>
                            <td>:</td>
                            <td>S/. {{ number_format($documento->total_pagar - $documento->notas->sum('mtoImpVenta'), 2) }}</td>
                        </tr>
                        <tr>
                            <td style="text-align: left">Total de cuotas</td>
                            <td>:</td>
                            <td>1</td>
                        </tr>
                    </table>
                    <table class="tbl-info-credito" style="margin-top: 2px;">
                        <tr>
                            <th style="text-align: center">N° Cuota</th>
                            <th style="text-align: center">Fec. Venc.</th>
                            <th style="text-align: center">Monto</th>
                        </tr>
                        <tr>
                            <td style="text-align: center">1</td>
                            <td style="text-align: center">{{ $documento->fecha_vencimiento }}</td>
                            <td style="text-align: center">{{ number_format($documento->total_pagar - $documento->notas->sum('mtoImpVenta'), 2) }}</td>
                        </tr>
                    </table>
                </div>
            @endif
            @if (!empty($documento->retencion))
                <div style="border: 1px solid black; padding: 2px; margin-top: 5px;">
                    <table class="tbl-info-retencion">
                        <tr>
                            <th style="text-align: left;">Información de la retención</th>
                        </tr>
                        <tr>
                            <td>
                                Base imponible de la Retención: &nbsp;&nbsp; S/. {{ number_format($documento->total + $documento->retencion->impRetenido, 2) }}
                            </td>
                            <td>
                                Porcentaje de retención: &nbsp;&nbsp; {{ $documento->clienteEntidad->tasa_retencion }}%
                            </td>
                            <td>
                                Monto de la Retención: &nbsp;&nbsp; S/. {{ number_format($documento->retencion->impRetenido, 2) }}
                            </td>
                        </tr>
                    </table>
                </div>
            @endif --}}
        </div>
       
     
        <div class="qr">
            @if($sale_document->ruta_qr)
                <img style="height:140px;object-fit: contain;" src="{{ public_path($sale_document->ruta_qr) }}">
            @endif
        </div>        
  

        <footer>
            <b>Sistema de reserva de campos deportivos 
                <a target="_blank" href="{{ (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                "https" : "http") . "://" . implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), 1))}}">
                    <em>
                        {{ (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . implode('.', array_slice(explode('.', $_SERVER['HTTP_HOST']), 1))}}
                    </em>
                </a>
            </b>
        </footer>
        
        {{-- <footer>
            <b>Para consultar el comprobante ingresar a <a target="_blank" href="{{ (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                "https" : "http") . "://" . $_SERVER['HTTP_HOST']."/buscar"}}"><em>{{ (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ?
                "https" : "http") . "://" . $_SERVER['HTTP_HOST']."/buscar"}}</em></a></b>
        </footer> --}}
    </body>

</html>
