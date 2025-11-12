<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>{{ 'R-' . str_pad($reservation->id, 8, '0', STR_PAD_LEFT) }}</title>
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
                    <p class="m-0 p-0 text-uppercase">RECIBO</p> 
                    <p class="m-0 p-0 text-uppercase">{{ 'R-' . str_pad($reservation->id, 8, '0', STR_PAD_LEFT) }}</p>
                </div>
            </div>
        </div><br>
        <div class="informacion">
            <table class="tbl-informacion">
                <tr>
                    <td>F. EMISIÓN</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->created_at)->format('d/m/Y H:i') }}</td>
                </tr>
                <tr>
                    <td>F. RESERVA.</td>
                    <td>:</td>
                    <td>{{ \Carbon\Carbon::parse($reservation->date)->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <td>HORA. RESERVA.</td>
                    <td>:</td>
                    <td>{{ $reservation->schedule->description}}</td>
                </tr>
                <tr>
                    <td>CLIENTE</td>
                    <td>:</td>
                    <td>{{ $reservation->customer->name }}</td>
                </tr>
                <tr>
                    <td class="text-uppercase">{{ $reservation->customer->type_document_abbreviation }}</td>
                    <td>:</td>
                    <td>{{ $reservation->customer->document_number }}</td>
                </tr>
                <tr>
                    <td>TELÉFONO</td>
                    <td>:</td>
                    <td class="text-uppercase">{{ $reservation->customer->phone }}</td>
                </tr>
                {{-- @if ($sale_document->observacion)
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
                </tr> --}}
            </table> 
        </div>
        <br>
        <div class="cuerpo">
            <table class="tbl-detalles text-uppercase" cellpadding="2" cellspacing="0">
                <thead>
                    <tr >
                        <th style="text-align: left; width: 15%;">CAMPO</th>
                        <th style="text-align: left; width: 10%;">MODALIDAD</th>
                        <th style="text-align: right; width: 10%;">TOTAL</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($reservation_detail as $item)

                        @if ($item->payment > 0)
                            <tr>
                                <td style="text-align: left">{{ $reservation->field_names }}</td>
                                <td style="text-align: left">{{ $reservation->modality }}</td>
                                <td style="text-align: right">{{ number_format($item->payment, 2) }}</td>
                            </tr>
                        @endif
                       
                    @endforeach
                   
                </tbody>
                <tfoot>
                        <tr>
                            <th colspan="2" style="text-align:right">Total Pagado: S/.</th>
                            <th style="text-align:right">{{ number_format($reservation_detail->sum('payment'), 2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="2" style="text-align:right">Saldo Pendiente: S/.</th>
                            <th style="text-align:right">{{ number_format($reservation->total - $reservation_detail->sum('payment'), 2) }}</th>
                        </tr>
                </tfoot>  
            </table>
            <br>
            {{-- <p class="p-0 m-0 text-uppercase text-cuerpo">SON: <b>{{ $sale_document->legend }}</b></p>  --}}
            <br>
        </div>
       
        <div class="qr">
            @if($reservation->qr_route)
                <img style="height:140px;object-fit: contain;" src="{{ public_path($reservation->qr_route) }}">
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
