<?php

namespace App\Http\Services\Tenant\Dashboard\Dashboard;

use App\Models\Company;
use App\Models\Herramientas\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardMarketService
{
    private array $data =   [];

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getData(string $anio, string $mes): object
    {

        $this->data['data_carousel']    =   $this->getDataCarousel($anio, $mes);

        $this->data['data_graficos']    =   $this->getDataGraficos($anio, $mes);

        $this->data['data_analisis']    =   $this->getDataAnalisis($anio, $mes);

        return (object)$this->data;
    }

    public function getDataCarousel(string $anio, string $mes): object
    {

        $ventas_mes             =   $this->getVentasMes($anio, $mes);
        $compras_mes            =   $this->getComprasMes($anio, $mes);
        $utilidad_bruta_mes     =   $this->getUtilidadBrutaMes($anio, $mes);
        $igv_mes                =   $this->getIgvMes($anio, $mes);
        $cant_comprobantes_mes  =   $this->getCantComprobantesMes($anio, $mes);
        $total_boletas_mes      =   $this->getTotalBoletasMes($anio, $mes);
        $total_facturas_mes     =   $this->getTotalFacturasMes($anio, $mes);
        $total_nota_credito_mes =   $this->getTotalNotaCreditoMes($anio,$mes);

        $data       =   (object)[
            'ventas_mes'            =>  $ventas_mes,
            'compras_mes'           =>  $compras_mes,
            'utilidad_bruta_mes'    =>  $utilidad_bruta_mes,
            'igv_mes'               =>  $igv_mes,
            'cant_comprobantes_mes' =>  $cant_comprobantes_mes,
            'total_boletas_mes'     =>  $total_boletas_mes,
            'total_facturas_mes'    =>  $total_facturas_mes,
            'total_nota_credito_mes' =>  $total_nota_credito_mes
        ];

        return $data;
    }

    public function getDataGraficos(string $anio, string $mes)
    {
        $data_graficos  =   (object)[
            'productos'         =>  $this->getProductosMes($anio, $mes),
            'cuentas_cobrar'    =>  $this->getCuentasCobrar(),
            // 'cuentas_pagar'     =>  $this->getCuentasPagar(),
            'ventas_vs_compras' =>  $this->getVentasVsComprasAnio($anio)
        ];
        return $data_graficos;
    }

    public function getDataAnalisis(string $anio, string $mes): array
    {
        $data_analisis  =   [
            'analisis_rentabilidad' =>  $this->getAnalisisRentabilidad($anio, $mes),
            'analisis_tributario'   =>  $this->getAnalisisTributario($anio, $mes),
            'analisis_eficiencia'   =>  $this->getAnalisisEficiencia($anio, $mes),
            'analisis_existencia'   =>  $this->getAnalisisExistencia()
        ];

        return $data_analisis;
    }

    public function getVentasMes(string $anio, string $mes): float
    {
        $ventas_mes =   DB::select('SELECT
                        SUM(v.total) as total
                        FROM sales_documents as v
                        WHERE
                        v.status = "ACTIVO"
                        AND v.sunat_status NOT IN("ANULADO","BAJA","ANULADO PARCIAL")
                        AND  YEAR(v.created_at) = ?
                        AND  MONTH(v.created_at) = ?
                        ', [$anio, $mes]);

        $ventas_mes =   round($ventas_mes[0]->total, 2);

        return $ventas_mes;
    }

    public function getIgvMes(string $anio, string $mes): float
    {
        $igv_mes    =   DB::select('SELECT
                        IFNULL(ROUND(SUM(v.igv_amount),2),0) as total
                        FROM sales_documents as v
                        WHERE
                        v.status = "ACTIVO"
                        AND v.sunat_status NOT IN("ANULADO","BAJA","ANULADO PARCIAL")
                        AND  YEAR(v.created_at) = ?
                        AND  MONTH(v.created_at) = ?
                        ', [$anio, $mes]);

        $igv_mes =   round($igv_mes[0]->total, 2);

        return $igv_mes;
    }

    public function getComprasMes(string $anio, string $mes): float
    {
        $compras_mes    =   DB::select('
                            SELECT
                            SUM(c.total) as total
                            FROM purchase_documents as c
                            WHERE
                            c.estado = "ACTIVO"
                            AND  YEAR(c.created_at) = ?
                            AND  MONTH(c.created_at) = ?
                            ', [$anio, $mes]);

        $compras_mes =   round($compras_mes[0]->total, 2);

        return $compras_mes;
    }

    /*
    public function getUtilidadBrutaMes(float $ventas_mes,float $compras_mes):float{
        return $ventas_mes - $compras_mes;
    }
    */

    public function getUtilidadBrutaMes(string $anio, string $mes): float
    {

        $consulta   =   DB::select(
            'SELECT
                        IFNULL(round(
                            SUM(
                                vd.net_quantity * ( round(vd.price_sale,2) - round(p.purchase_price,2) )
                            )
                        ,2),0) as utilidad_bruta_mes
                        FROM sales_documents_details AS vd
                        JOIN sales_documents AS v ON v.id = vd.sale_document_id
                        JOIN products AS p on p.id = vd.product_id
                        WHERE
                        v.status = "ACTIVO"
                        AND v.sunat_status NOT IN("ANULADO","ANULADO PARCIAL")
                        AND YEAR(v.created_at) = ?
                        AND MONTH(v.created_at) = ?',
            [$anio, $mes]
        );

        return $consulta[0]->utilidad_bruta_mes;
    }

    public function getCantComprobantesMes(string $anio, string $mes): int
    {
        $comprobantes_mes =   DB::select('SELECT
                        COUNT(v.id) as cant
                        FROM sales_documents as v
                        WHERE
                        v.status = "ACTIVO"
                        AND v.sunat_status NOT IN("ANULADO","BAJA","ANULADO PARCIAL")
                        AND  YEAR(v.created_at) = ?
                        AND  MONTH(v.created_at) = ?
                        ', [$anio, $mes]);

        $comprobantes_mes =   $comprobantes_mes[0]->cant;

        return $comprobantes_mes;
    }

    public function getTotalBoletasMes(string $anio, string $mes): float
    {
        $boletas_mes    =   DB::select('SELECT
                            SUM(v.total) as total
                            FROM sales_documents as v
                            WHERE
                            v.status = "ACTIVO"
                            AND v.type_sale_code = "03"
                            AND v.sunat_status NOT IN("ANULADO","BAJA","ANULADO PARCIAL")
                            AND  YEAR(v.created_at) = ?
                            AND  MONTH(v.created_at) = ?
                            ', [$anio, $mes]);

        $boletas_mes =   round($boletas_mes[0]->total, 2);

        return $boletas_mes;
    }

    public function getTotalFacturasMes(string $anio, string $mes): float
    {
        $facturas_mes    =   DB::select('
                            SELECT
                            SUM(v.total) as total
                            FROM sales_documents as v
                            WHERE
                            v.status = "ACTIVO"
                            AND v.type_sale_code = "01"
                            AND v.sunat_status NOT IN("ANULADO","BAJA","ANULADO PARCIAL")
                            AND  YEAR(v.created_at) = ?
                            AND  MONTH(v.created_at) = ?
                            ', [$anio, $mes]);

        $facturas_mes =   round($facturas_mes[0]->total, 2);

        return $facturas_mes;
    }

    public function getTotalNotaCreditoMes(string $anio, string $mes): float
    {
        $notas_mes    =   DB::select('SELECT
                            COALESCE(SUM(nc.total), 0)  as total
                            FROM credit_notes as nc
                            WHERE
                            nc.sunat_status != "RECHAZADO"
                            AND  YEAR(nc.created_at) = ?
                            AND  MONTH(nc.created_at) = ?
                            ', [$anio, $mes]);

        $notas_mes =   round($notas_mes[0]->total, 2);

        return $notas_mes;
    }

    public function getProductosMes(string $anio, string $mes)
    {

        $productos  =   DB::table('sales_documents_details as sdd')
            ->join('sales_documents as sd', 'sd.id', '=', 'sdd.sale_document_id')
            ->join('products as p', 'p.id', '=', 'sdd.product_id')
            ->select(
                'p.id',
                'p.name',
                DB::raw('SUM(sdd.net_quantity) as cantidad_vendida')
            )
            ->whereYear('sd.created_at', $anio)
            ->whereMonth('sd.created_at', $mes)
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('cantidad_vendida')
            ->limit(10)
            ->get();

        $formateado = $productos->map(function ($item) {
            return [$item->name, (float) $item->cantidad_vendida];
        });

        return  $formateado;
    }

    public function getCuentasCobrar(): object
    {

        $cuentas_clientes   =   DB::select('SELECT
                                CONCAT(c.type_document_abbreviation,":",c.document_number,"-",c.name) as cliente_nombre,
                                round(SUM(cc.balance),2) as cliente_saldo
                                FROM customer_accounts as cc
                                INNER JOIN erptaller.customers as c on c.id = cc.customer_id
                                WHERE
                                cc.status <> "ANULADO"
                                GROUP BY c.type_document_abbreviation,c.document_number,c.name');


        $cuentas_clientes_array =   [];
        foreach ($cuentas_clientes as $cuenta) {
            $cuentas_clientes_array[]   =   [
                $cuenta->cliente_nombre,
                round((float) $cuenta->cliente_saldo, 2)
            ];
        }

        $totales        =   DB::select('SELECT
                                ROUND(IFNULL(SUM(cc.balance), 0),2) AS pendiente,
                                ROUND(IFNULL(SUM(cc.amount - cc.balance), 0),2) AS cobrado,
                                ROUND(IFNULL(SUM(cc.amount), 0), 2) AS total
                            FROM customer_accounts as cc');

        $resultado  =   (object)['cuentas' => $cuentas_clientes_array, 'totales' => $totales[0]];

        return $resultado;
    }

    public function getCuentasPagar(): array
    {

        // $cuentas_cobrar =   DB::select('
        //                         select
        //                             IFNULL(SUM(cc.saldo), 0) AS pendiente,
        //                             IFNULL(SUM(cc.monto - cc.saldo), 0) AS cobrado,
        //                             IFNULL(SUM(cc.monto), 0) AS total
        //                         from cuentas_cliente as cc
        //                         where
        //                         YEAR(cc.created_at) = ?
        //                         AND MONTH(cc.created_at) = ?
        //                     ',[$anio,$mes]);

        $totales    =   (object)[
            'cobrado'   =>      round((float) 0, 2),
            'pendiente' =>      round((float) 0, 2),
            'total'     =>      round((float) 0, 2)
        ];

        $cuentas    =   [];

        $cuentas_pagar  =   [
            'cuentas'   =>  $cuentas,
            'totales' => $totales
        ];

        return $cuentas_pagar;
    }

    public function getStockMin(Request $request)
    {

        $categoria_id       =   $request->get('categoria_id');
        $marca_id           =   $request->get('marca_id');
        $establecimiento    =   $request->get('establecimiento');

        $productos = DB::table('products as p')
            ->join('brands as m', 'm.id', '=', 'p.brand_id')
            ->join('categories as c', 'c.id', '=', 'p.category_id')
            ->join('warehouse_products as ap', 'ap.product_id', 'p.id')
            ->select(
                'p.id',
                'p.brand_id',
                'p.category_id',
                'p.unit_id',
                'p.name',
                'p.code_bar',
                'p.sale_price',
                'p.purchase_price',
                'p.stock_min',
                'm.name as brand_name',
                'c.name as category_name',
                'p.unit_symbol AS unit_measurement',
                'p.unit_symbol',
                'p.status',
                'ap.stock'
            )
            ->where('p.status', 'ACTIVO')
            ->where('ap.warehouse_id', 1)
            ->whereColumn('ap.stock', '<', 'p.stock_min');


        if ($categoria_id) {
            $productos  =   $productos->where('p.category_id', $categoria_id);
        }

        if ($marca_id) {
            $productos  =   $productos->where('p.brand_id', $marca_id);
        }

        $productos  =   $productos->get();


        return $productos;
    }

    public function getVentasVsComprasAnio(string $anio): array
    {

        DB::statement("SET lc_time_names = 'es_ES'");

        $ventas =   DB::table('sales_documents as v')
            ->select(
                DB::raw('MONTH(v.created_at) as mes'),
                DB::raw('UPPER(MONTHNAME(v.created_at)) as nombre_mes'),
                DB::raw('SUM(v.total) as total_mes')
            )
            ->where('v.status', 'ACTIVO')
            ->whereYear('v.created_at', $anio)
            ->whereNotIn('v.sunat_status', ['ANULADO', 'ANULADO PARCIAL', 'BAJA'])
            ->groupBy(DB::raw('MONTH(v.created_at)'), DB::raw('UPPER(MONTHNAME(v.created_at))'))
            ->orderBy('mes')
            ->get();

        $todos_los_meses = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE'
        ];

        $ventas_por_mes = array_fill(1, 12, 0);

        foreach ($ventas as $venta) {
            $ventas_por_mes[$venta->mes] = $venta->total_mes;
        }

        $res_ventas = [];
        foreach ($todos_los_meses as $mes_num => $mes_nombre) {
            // $resultados[] = [
            //     'mes' => $mes_num,
            //     'nombre_mes' => $mes_nombre,
            //     'total_mes' => $ventas_por_mes[$mes_num],
            // ];
            $res_ventas[]   =   round($ventas_por_mes[$mes_num], 2);
        }


        //======== COMPRAS ==========
        $compras =   DB::table('purchase_documents as c')
            ->select(
                DB::raw('MONTH(c.created_at) as mes'),
                DB::raw('UPPER(MONTHNAME(c.created_at)) as nombre_mes'),
                DB::raw('SUM(c.total) as total_mes')
            )
            ->where('c.estado', 'ACTIVO')
            ->whereYear('c.created_at', $anio)
            ->groupBy(DB::raw('MONTH(c.created_at)'), DB::raw('UPPER(MONTHNAME(c.created_at))'))
            ->orderBy('mes')
            ->get();


        $compras_por_mes = array_fill(1, 12, 0);

        foreach ($compras as $compra) {
            $compras_por_mes[$compra->mes] = $compra->total_mes;
        }


        $res_compras = [];
        foreach ($todos_los_meses as $mes_num => $mes_nombre) {
            $res_compras[]   =   round($compras_por_mes[$mes_num], 2);
        }


        $resultados['ventas']       =   $res_ventas;
        $resultados['compras']      =   $res_compras;

        return $resultados;
    }

    public function getAnalisisRentabilidad(string $anio, string $mes): object
    {
        $facturas               =   $this->getFacturasRentabilidad($anio, $mes);
        $boletas                =   $this->getBoletasRentabilidad($anio, $mes);
        $notas_credito          =   $this->getNotasCreditoRentabilidad($anio, $mes);
        $totales                =   $this->getTotalesRentabilidad($facturas, $boletas, $notas_credito);

        return (object)['datos' => [$facturas, $boletas, $notas_credito], 'totales' => $totales];
    }

    public function getAnalisisTributario(string $anio, string $mes): object
    {

        $ventas_tributario      =   $this->getVentasTributario($anio, $mes);
        $compras_tributario     =   $this->getComprasAfectasTributario($anio, $mes);
        $renta                  =   $this->getRentaTributario($ventas_tributario->igv, $compras_tributario->igv, $ventas_tributario->total);

        $data   =   [
            (object)[
                'descripcion'       =>  'VALOR VENTA',
                'ventas'            =>  round($ventas_tributario->subtotal, 2),
                'compras_afectas'   =>  round($compras_tributario->subtotal, 2),
                'compras_inafectas' =>  0
            ],
            (object)[
                'descripcion'  => 'IGV',
                'ventas'            =>  round($ventas_tributario->igv, 2),
                'compras_afectas'   =>  round($compras_tributario->igv, 2),
                'compras_inafectas' =>  0
            ],
            (object)[
                'descripcion'       => 'TOTAL',
                'ventas'            =>  round($ventas_tributario->total, 2),
                'compras_afectas'   =>  round($compras_tributario->total, 2),
                'compras_inafectas' =>  0
            ],
        ];

        $res    =   (object)['data' => $data, 'renta' => $renta];

        return $res;
    }

    public function getRentaTributario(float $igv_ventas, float $igv_compras, float $total_ventas): object
    {
        $renta  =   Company::find(1)->renta??1;
        return (object)[
            'igv_pagar' =>  $igv_ventas - $igv_compras,
            'renta'     =>  round($total_ventas * $renta, 2)
        ];
    }

    public function getVentasTributario(string $anio, string $mes): object
    {
        $ventas_tributario  =   DB::select('SELECT
                                    IFNULL(ROUND(SUM(v.subtotal),2),0) as subtotal,
                                    IFNULL(ROUND(SUM(v.igv_amount),2),0) as igv,
                                    IFNULL(ROUND(SUM(v.total),2),0) as total
                                    from sales_documents as v
                                    where
                                    v.status = "ACTIVO"
                                    AND v.sunat_status NOT IN("ANULADO","ANULADO PARCIAL","BAJA")
                                    AND YEAR(v.created_at) = ?
                                    AND MONTH(v.created_at) = ?
                                ', [$anio, $mes])[0];

        return $ventas_tributario;
    }

    public function getComprasAfectasTributario(string $anio, string $mes): object
    {
        $compras_tributario  =   DB::selectOne('SELECT
                                    IFNULL(ROUND(SUM(c.subtotal),2),0) as subtotal,
                                    IFNULL(ROUND(SUM(c.amount_igv),2),0) as igv,
                                    IFNULL(ROUND(SUM(c.total),2),0) as total
                                    from purchase_documents as c
                                    where
                                    c.estado = "ACTIVO"
                                    AND YEAR(c.created_at) = ?
                                    AND MONTH(c.created_at) = ?
                                ', [$anio, $mes]);

        return $compras_tributario;
    }

    public function getComprasInafectasTributario(string $anio, string $mes): null
    {
        return null;
    }

    public function getBoletasRentabilidad(string $anio, string $mes): object
    {

        $boletas_rentabilidad   =   $this->getComprobanteRentabilidad($anio, $mes, '03');
        return $boletas_rentabilidad;
    }

    public function getFacturasRentabilidad(string $anio, string $mes): object
    {

        $facturas_rentabiidad   =   $this->getComprobanteRentabilidad($anio, $mes, '01');

        return $facturas_rentabiidad;
    }

    public function getNotasCreditoRentabilidad(string $anio, string $mes): object
    {
        $resultado  =   DB::select(
            'SELECT
                            COUNT(DISTINCT nc.id) as operaciones,
                            SUM(nc.total) as ventas,
                            (
                                SELECT
                                    SUM(IFNULL(round(p.purchase_price,2) * round(ncd.quantity,2), 0))
                                FROM credit_notes_details ncd
                                JOIN products p ON p.id = ncd.product_id
                                JOIN credit_notes nc2 ON nc2.id = ncd.credit_note_id
                                WHERE
                                    YEAR(nc2.created_at) = ?
                                    AND MONTH(nc2.created_at) = ?
                            ) as costos
                        FROM credit_notes as nc
                        WHERE
                            YEAR(nc.created_at) = ?
                            AND MONTH(nc.created_at) = ?',
            [
                $anio,
                $mes,
                $anio,
                $mes
            ]
        );

        $data   =   $resultado[0];

        return (object)[
            'documento'         => 'NOTAS CRÉDITO',
            'operaciones'       => (int)$data->operaciones,
            'ventas'            => (float)$data->ventas,
            'costos'            => round($data->costos, 2),
            'utilidad_bruta'    => round((float)$data->ventas - (float)$data->costos, 2)
        ];
    }

    public function getComprobanteRentabilidad(string $anio, string $mes, string $codigo_doc): object
    {

        $resultado  =   DB::select(
            '
                        SELECT
                            COUNT(DISTINCT v.id) as operaciones,
                            SUM(v.total) as ventas,
                            (
                                SELECT
                                    SUM(IFNULL(round(p.purchase_price,2) * round(vd.quantity,2), 0))
                                FROM sales_documents_details vd
                                JOIN products p ON p.id = vd.product_id
                                JOIN sales_documents v2 ON v2.id = vd.sale_document_id
                                WHERE
                                    v2.status = "ACTIVO"
                                    AND v2.type_sale_code = ?
                                    AND v2.sunat_status NOT IN("ANULADO", "BAJA", "ANULADO PARCIAL")
                                    AND YEAR(v2.created_at) = ?
                                    AND MONTH(v2.created_at) = ?
                            ) as costos
                        FROM sales_documents as v
                        WHERE
                            v.status = "ACTIVO"
                            AND v.type_sale_code = ?
                            AND v.sunat_status NOT IN("ANULADO", "BAJA", "ANULADO PARCIAL")
                            AND YEAR(v.created_at) = ?
                            AND MONTH(v.created_at) = ?',
            [
                $codigo_doc,
                $anio,
                $mes,
                $codigo_doc,
                $anio,
                $mes
            ]
        );

        $data       =   $resultado[0];
        $documento  =   '';
        if ($codigo_doc === '01') {
            $documento  =   'FACTURAS';
        }
        if ($codigo_doc === '03') {
            $documento  =   'BOLETAS';
        }

        return (object)[
            'documento'         => $documento,
            'operaciones'       => (int)$data->operaciones,
            'ventas'            => (float)$data->ventas,
            'costos'            => round($data->costos, 2),
            'utilidad_bruta'    => round((float)$data->ventas - (float)$data->costos, 2)
        ];
    }

    public function getTotalesRentabilidad(object $facturas, object $boletas, object $notas_credito): object
    {
        return (object)[
            'documento'         =>  'TOTAL',
            'operaciones'       =>  $facturas->operaciones + $boletas->operaciones + $notas_credito->operaciones,
            'ventas'            =>  $facturas->ventas + $boletas->ventas - $notas_credito->ventas,
            'costos'            =>  $facturas->costos + $boletas->costos - $notas_credito->costos,
            'utilidad_bruta'    =>  $facturas->utilidad_bruta + $boletas->utilidad_bruta - $notas_credito->utilidad_bruta
        ];
    }

    public function getAnalisisEficiencia(string $anio, string $mes): object
    {

        $res    =   (object)[
            'saldo_cobranza'        =>  $this->getSaldoCobranzaAnt($anio, $mes),
            'creditos_cobranza'     =>  $this->getCreditosCobranza($anio, $mes),
            'cobranza'              =>  $this->getCobranza($anio, $mes),
            'acumulado_cobranza'    =>  $this->getSaldoCobranza($anio, $mes),

            // 'saldo_pagar'           =>  $this->getSaldoPagarAnt($anio, $mes),
            // 'creditos_pagar'        =>  $this->getCreditosPagarMes($anio, $mes),
            // 'pagar'                 =>  $this->getPagarMes($anio, $mes),
            // 'acumulado_pagar'       =>  $this->getSaldoPagar($anio, $mes)
        ];

        return $res;
    }

    public function getCreditosCobranza(string $anio, string $mes): float
    {

        $res    =   DB::select('SELECT
                    IFNULL(SUM(v.total),0) as creditos
                    FROM sales_documents as v
                    WHERE v.status = "ACTIVO"
                    AND v.sunat_status NOT IN("ANULADO","ANULADO PARCIAL","BAJA")
                    AND v.payment_condition_id <> 1
                    AND YEAR(v.created_at) = ?
                    AND MONTH(v.created_at) = ?', [$anio, $mes]);

        return  round($res[0]->creditos, 2);
    }

    public function getCobranza(string $anio, string $mes)
    {
        //--ccd.status <> "ANULADO"
        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.amount),0) as cobranza
                        FROM customer_accounts_details AS ccd
                        WHERE
                        YEAR(ccd.created_at) = ?
                        AND MONTH(ccd.created_at) = ?', [$anio, $mes]);

        return  round($consulta[0]->cobranza, 2);
    }

    public function getSaldoCobranza(string $anio, string $mes)
    {
        $saldo_ant  =   $this->getSaldoCobranzaAnt($anio, $mes);
        $credito    =   $this->getCreditosCobranza($anio, $mes);
        $cobrado    =   $this->getCobranza($anio, $mes);

        $saldo      =   $saldo_ant + $credito - $cobrado;

        return  round($saldo, 2);
    }

    public function getSaldoCobranzaAnt(string $anio, string $mes)
    {

        $credito    =   $this->getCreditosCobranzaAnt($anio, $mes);

        $cobrado    =   $this->getCobranzaAnt($anio, $mes);

        return $credito - $cobrado;
    }

    public function getAnalisisExistencia(): object
    {
        $data_existencias   =   [
            'stock_valorizado' =>  $this->getStockValorizado()
        ];

        return (object)$data_existencias;
    }

    public function getSaldoPagarAnt(string $anio, string $mes)
    {

        $credito    =   $this->getCreditosPagarAnt($anio, $mes);

        $cobrado    =   $this->getPagarAnt($anio, $mes);

        return $credito - $cobrado;
    }

    public function getCreditosPagarMes(string $anio, string $mes): float
    {

        $res    =   DB::select('SELECT
                    IFNULL(SUM(c.importe_total),0) as creditos
                    FROM compras as c
                    WHERE c.estado = "ACTIVO"
                    AND c.condicion_pago_id <> 1
                    AND YEAR(c.created_at) = ?
                    AND MONTH(c.created_at) = ?', [$anio, $mes]);

        return  round($res[0]->creditos, 2);
    }

    public function getStockValorizado(): float
    {
        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ap.stock * p.purchase_price),0) as stock_valorizado
                        FROM warehouse_products as ap
                        JOIN products as p on p.id = ap.product_id
                        WHERE ap.warehouse_id = 1
                        AND p.status = "ACTIVO"');
        return round($consulta[0]->stock_valorizado, 2);
    }

    public function getPagarMes(string $anio, string $mes)
    {

        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.monto),0) as pagar
                        FROM cuentas_proveedor_detalle AS ccd
                        WHERE
                        ccd.estado <> "ANULADO"
                        AND YEAR(ccd.created_at) = ?
                        AND MONTH(ccd.created_at) = ?', [$anio, $mes]);

        return  round($consulta[0]->pagar, 2);
    }

    public function getSaldoPagar(string $anio, string $mes)
    {

        $saldo_ant  =   $this->getSaldoPagarAnt($anio, $mes);
        $credito    =   $this->getCreditosPagarMes($anio, $mes);
        $cobrado    =   $this->getPagarMes($anio, $mes);

        $saldo      =   $saldo_ant + $credito - $cobrado;

        return  round($saldo, 2);
    }

    public function getCreditosPagarAnt(string $anio, string $mes): float
    {

        $res    =   DB::select('SELECT
                    IFNULL(SUM(c.importe_total),0) as creditos
                    FROM compras as c
                    WHERE c.estado = "ACTIVO"
                    AND c.condicion_pago_id <> 1
                    AND YEAR(c.created_at) <= ?
                    AND MONTH(c.created_at) < ?', [$anio, $mes]);

        return  round($res[0]->creditos, 2);
    }

    public function getPagarAnt(string $anio, string $mes)
    {

        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.monto),0) as pagar
                        FROM cuentas_proveedor_detalle AS ccd
                        WHERE
                        ccd.estado <> "ANULADO"
                        AND YEAR(ccd.created_at) <= ?
                        AND MONTH(ccd.created_at) < ?', [$anio, $mes]);

        return  round($consulta[0]->pagar, 2);
    }

    public function getCobranzaAnt(string $anio, string $mes)
    {
                        //--ccd.status <> "ANULADO"

        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.amount),0) as cobranza
                        FROM customer_accounts_details AS ccd
                        WHERE
                        YEAR(ccd.created_at) <= ?
                        AND MONTH(ccd.created_at) < ?', [$anio, $mes]);

        return  round($consulta[0]->cobranza, 2);
    }

    public function getCreditosCobranzaAnt(string $anio, string $mes): float
    {

        $res    =   DB::select('SELECT
                    IFNULL(SUM(v.total),0) as creditos
                    FROM sales_documents as v
                    WHERE v.status = "ACTIVO"
                    AND v.sunat_status NOT IN("ANULADO","ANULADO PARCIAL","BAJA")
                    AND v.payment_condition_id <> 1
                    AND YEAR(v.created_at) <= ?
                    AND MONTH(v.created_at) < ?', [$anio, $mes]);

        return  round($res[0]->creditos, 2);
    }
}
