<?php

namespace App\Http\Services\Tenant\Dashboard\Dashboard;

use App\Models\Herramientas\Empresa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardGrifoService
{
    private array $data =   [];

    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function getData(string $anio,string $mes):object{

        $this->data['data_carousel']    =   $this->getDataCarousel($anio,$mes);

        $this->data['data_graficos']    =   $this->getDataGraficos($anio,$mes);

        $this->data['data_analisis']    =   $this->getDataAnalisis($anio,$mes);

        return (object)$this->data;
    }

    public function getDataGraficos(string $anio,string $mes){
        $data_graficos  =   (object)[
                                'productos'         =>  $this->getProductosMes($anio,$mes),
                                'cuentas_cobrar'    =>  $this->getCuentasCobrar($anio,$mes),
                                'cuentas_pagar'     =>  $this->getCuentasPagar($anio,$mes),
                                'ventas_vs_compras' =>  $this->getVentasVsComprasAnio($anio)
                            ];
        return $data_graficos;
    }

    public function getDataCarousel(string $anio,string $mes):object{

        $ventas_mes             =   $this->getVentasMes($anio,$mes);
        $compras_mes            =   $this->getComprasMes($anio,$mes);
        $utilidad_bruta_mes     =   $this->getUtilidadBrutaMes($anio,$mes);
        $igv_mes                =   $this->getIgvMes($anio,$mes);
        $cant_comprobantes_mes  =   $this->getCantComprobantesMes($anio,$mes);
        $total_boletas_mes      =   $this->getTotalBoletasMes($anio,$mes);
        $total_facturas_mes     =   $this->getTotalFacturasMes($anio,$mes);
        $total_nota_credito_mes =   $this->getTotalNotaCreditoMes($anio,$mes);

        $data       =   (object)[
                            'ventas_mes'            =>  $ventas_mes,
                            'compras_mes'           =>  $compras_mes,
                            'utilidad_bruta_mes'    =>  $utilidad_bruta_mes,
                            'igv_mes'               =>  $igv_mes,
                            'cant_comprobantes_mes' =>  $cant_comprobantes_mes,
                            'total_boletas_mes'     =>  $total_boletas_mes,
                            'total_facturas_mes'    =>  $total_facturas_mes,
                            'total_nota_credito_mes'=>  $total_nota_credito_mes
                        ];

        return $data;
    }

    public function getDataAnalisis(string $anio,string $mes):array{
        $data_analisis  =   [
            'analisis_rentabilidad' =>  $this->getAnalisisRentabilidad($anio,$mes),
            'analisis_tributario'   =>  $this->getAnalisisTributario($anio,$mes),
            'analisis_eficiencia'   =>  $this->getAnalisisEficiencia($anio,$mes),
            'analisis_existencia'   =>  $this->getAnalisisExistencia()
        ];

        return $data_analisis;
    }

    public function getVentasMes(string $anio,string $mes):float {
        $ventas_mes =   DB::select('
                        SELECT
                        SUM(v.importe_total) as total
                        FROM ventas_grifo as v
                        WHERE
                        v.estado = "ACTIVO"
                        AND v.estado_sunat NOT IN("ANULADO","BAJA","DEVOLUCION POR ITEM")
                        AND  YEAR(v.created_at) = ?
                        AND  MONTH(v.created_at) = ?
                        ',[$anio,$mes]);

        $ventas_mes =   round($ventas_mes[0]->total,2);

        return $ventas_mes;
    }

    public function getIgvMes(string $anio,string $mes):float{
        $igv_mes    =   DB::select('
                        SELECT
                        SUM(v.igv_monto) as total
                        FROM ventas_grifo as v
                        WHERE
                        v.estado = "ACTIVO"
                        AND v.estado_sunat NOT IN("ANULADO","BAJA","DEVOLUCION POR ITEM")
                        AND  YEAR(v.created_at) = ?
                        AND  MONTH(v.created_at) = ?
                        ',[$anio,$mes]);

        $igv_mes =   round($igv_mes[0]->total,2);

        return $igv_mes;
    }

    public function getComprasMes(string $anio,string $mes):float {
        $compras_mes    =   DB::select('
                            SELECT
                            SUM(c.importe_total) as total
                            FROM compras_grifo as c
                            WHERE
                            c.estado = "ACTIVO"
                            AND  YEAR(c.created_at) = ?
                            AND  MONTH(c.created_at) = ?
                            ',[$anio,$mes]);

        $compras_mes =   round($compras_mes[0]->total,2);

        return $compras_mes;
    }

    /*
    public function getUtilidadBrutaMes(float $ventas_mes,float $compras_mes):float{
        return $ventas_mes - $compras_mes;
    }
    */

    public function getUtilidadBrutaMes(string $anio,string $mes):float{

        $consulta   =   DB::select('
                            select
                                round(
                                    COALESCE(SUM(
                                        COALESCE(vd.cantidad_neta, 0) * (
                                            round(COALESCE(vd.precio_venta, 0), 2) - round(p.precio_compra, 2)
                                        )
                                    ), 0), 2) as utilidad_bruta_mes
                            from ventas_grifo as v
                            left join ventas_detalle_grifo as vd on v.id = vd.venta_id
                            join productos_grifo as p on p.id = vd.producto_id
                            where
                                v.estado = "ACTIVO"
                                AND v.estado_sunat NOT IN("ANULADO","DEVOLUCION POR ITEM")
                                AND YEAR(v.created_at) = ?
                                AND MONTH(v.created_at) = ?
                                AND v.tipo_venta_nombre <> "VENTA FICTICIA"
                        ', [$anio, $mes]);

        return $consulta[0]->utilidad_bruta_mes;

    }

    public function getCantComprobantesMes(string $anio,string $mes):int{
        $comprobantes_mes =   DB::select('
                        SELECT
                        COUNT(v.id) as cant
                        FROM ventas_grifo as v
                        WHERE
                        v.estado = "ACTIVO"
                        AND v.estado_sunat NOT IN("ANULADO","BAJA","DEVOLUCION POR ITEM")
                        AND  YEAR(v.created_at) = ?
                        AND  MONTH(v.created_at) = ?
                        ',[$anio,$mes]);

        $comprobantes_mes =   $comprobantes_mes[0]->cant;

        return $comprobantes_mes;
    }

    public function getTotalBoletasMes(string $anio,string $mes):float {
        $boletas_mes    =   DB::select('
                            SELECT
                            SUM(v.total) as total
                            FROM ventas_grifo as v
                            WHERE
                            v.estado = "ACTIVO"
                            AND v.tipo_venta_codigo = "03"
                            AND v.estado_sunat NOT IN("ANULADO","BAJA","DEVOLUCION POR ITEM")
                            AND  YEAR(v.created_at) = ?
                            AND  MONTH(v.created_at) = ?
                            ',[$anio,$mes]);

        $boletas_mes =   round($boletas_mes[0]->total,2);

        return $boletas_mes;
    }

    public function getTotalFacturasMes(string $anio,string $mes):float {
        $facturas_mes    =   DB::select('
                            SELECT
                            SUM(v.total) as total
                            FROM ventas_grifo as v
                            WHERE
                            v.estado = "ACTIVO"
                            AND v.tipo_venta_codigo = "01"
                            AND v.estado_sunat NOT IN("ANULADO","BAJA","DEVOLUCION POR ITEM")
                            AND  YEAR(v.created_at) = ?
                            AND  MONTH(v.created_at) = ?
                            ',[$anio,$mes]);

        $facturas_mes =   round($facturas_mes[0]->total,2);

        return $facturas_mes;
    }

    public function getTotalNotaCreditoMes(string $anio,string $mes):float{
        $notas_mes    =   DB::select('
                            SELECT
                            SUM(nc.total) as total
                            FROM notas_credito as nc
                            WHERE
                            nc.estado != "RECHAZADO"
                            AND  YEAR(nc.created_at) = ?
                            AND  MONTH(nc.created_at) = ?
                            ',[$anio,$mes]);

        $notas_mes =   round($notas_mes[0]->total,2);

        return $notas_mes;
    }

    public function getProductosMes(string $anio,string $mes){

        $productos  =   DB::table('ventas_detalle_grifo as vdg')
                        ->join('ventas_grifo as vg', 'vg.id', '=', 'vdg.venta_id')
                        ->join('productos_grifo as pg', 'pg.id', '=', 'vdg.producto_id')
                        ->select(
                            'pg.id',
                            'pg.nombre',
                            DB::raw('SUM(vdg.cantidad_neta) as cantidad_vendida')
                        )
                        ->whereYear('vg.created_at', $anio)
                        ->whereMonth('vg.created_at', $mes)
                        ->groupBy('pg.id', 'pg.nombre')
                        ->orderByDesc('cantidad_vendida')
                        ->limit(10)
                        ->get();

        $formateado = $productos->map(function ($item) {
            return [$item->nombre, (float) $item->cantidad_vendida];
        });

        return  $formateado;
    }

    public function getCuentasCobrar(string $anio,string $mes):object{

        $cuentas_clientes   =   DB::select('select
                                CONCAT(c.tipo_doc_nombre,":",c.documento_numero,"-",c.nombre) as cliente_nombre,
                                round(SUM(cc.saldo),2) as cliente_saldo
                                from cuentas_cliente_grifo as cc
                                inner join clientes_grifo as c on c.id = cc.cliente_id
                                where
                                YEAR(cc.created_at) = ?
                                AND MONTH(cc.created_at) = ?
                                GROUP BY c.tipo_doc_nombre,c.documento_numero,c.nombre',[$anio,$mes]);

        $cuentas_clientes_array =   [];
        foreach ($cuentas_clientes as $cuenta) {
            $cuentas_clientes_array[]   =   [
                                                $cuenta->cliente_nombre,
                                                round((float) $cuenta->cliente_saldo, 2)
                                            ];
        }

        $totales        =   DB::select('
                                select
                                    round(IFNULL(SUM(cc.saldo), 0),2) AS pendiente,
                                    round(IFNULL(SUM(cc.monto - cc.saldo), 0),2) AS cobrado,
                                    ROUND(IFNULL(SUM(cc.monto), 0), 2) AS total
                                from cuentas_cliente_grifo as cc
                                where
                                YEAR(cc.created_at) = ?
                                AND MONTH(cc.created_at) = ?
                            ',[$anio,$mes]);

        $resultado  =   (object)['cuentas'=>$cuentas_clientes_array,'totales'=>$totales[0]];

        return $resultado;
    }


    public function getCuentasPagar():object{

        $cuentas_proveedores    =   DB::select('select
                                    CONCAT(pg.tipo_doc_nombre,":",pg.documento_numero,"-",pg.nombre) as proveedor_nombre,
                                    round(SUM(cpg.saldo),2) as proveedor_saldo
                                    from cuentas_proveedor_grifo as cpg
                                    inner join proveedores_grifo as pg on pg.id = cpg.proveedor_id
                                    GROUP BY pg.tipo_doc_nombre,pg.documento_numero,pg.nombre');

        $cuentas_proveedores_array  =   [];
        foreach ($cuentas_proveedores as $cuenta) {
            $cuentas_proveedores_array[]    =   [
                                                    $cuenta->proveedor_nombre,
                                                    round((float) $cuenta->proveedor_saldo, 2)
                                                ];
        }

        $totales        =   DB::select('
                            select
                                ROUND(IFNULL(SUM(cpg.saldo), 0),2) AS pendiente,
                                ROUND(IFNULL(SUM(cpg.monto - cpg.saldo), 0),2) AS cobrado,
                                ROUND(IFNULL(SUM(cpg.monto), 0), 2) AS total
                            from cuentas_proveedor_grifo as cpg
                            ');

        $resultado  =   (object)['cuentas'=>$cuentas_proveedores_array,'totales'=>$totales[0]];

        return $resultado;
    }

    public function getStockMin(Request $request){

        $categoria_id       =   $request->get('categoria_id');
        $marca_id           =   $request->get('marca_id');
        $establecimiento    =   $request->get('establecimiento');

        $productos = DB::table('productos_grifo as p')
                    ->join('marcas_grifo as m', 'm.id', '=', 'p.marca_id')
                    ->join('categorias_grifo as c', 'c.id', '=', 'p.categoria_id')
                    ->join('tablas_generales_detalles as tgd', 'tgd.id', '=', 'p.unidad_medida_id')
                    ->join('almacen_productos_grifo as ap','ap.producto_id','p.id')
                    ->select(
                        'p.id',
                        'p.marca_id',
                        'p.categoria_id',
                        'p.unidad_medida_id',
                        'p.nombre',
                        'p.codigo_barras',
                        'p.precio_venta',
                        'p.precio_compra',
                        'p.stock_minimo',
                        'm.descripcion as marca_nombre',
                        'c.descripcion as categoria_nombre',
                        'tgd.descripcion as unidad_medida_nombre',
                        'p.estado',
                        'p.tipo_producto',
                        'ap.stock'
                    )
                    ->where('p.subsistema',$establecimiento)
                    ->where('p.estado', 'ACTIVO')
                    ->where('ap.almacen_id',1)
                    ->whereColumn('ap.stock', '<', 'p.stock_minimo');


        if($categoria_id){
            $productos  =   $productos->where('p.categoria_id',$categoria_id);
        }

        if($marca_id){
            $productos  =   $productos->where('p.marca_id',$marca_id);
        }

        $productos  =   $productos->get();


        return $productos;
    }

    public function getVentasVsComprasAnio(string $anio):array{

        DB::statement("SET lc_time_names = 'es_ES'");

        $ventas =   DB::table('ventas_grifo as v')
                    ->select(
                        DB::raw('MONTH(v.created_at) as mes'),
                        DB::raw('UPPER(MONTHNAME(v.created_at)) as nombre_mes'),
                        DB::raw('SUM(v.importe_total) as total_mes')
                    )
                    ->where('v.estado','ACTIVO')
                    ->whereYear('v.created_at', $anio)
                    ->whereNotIn('v.estado_sunat', ['ANULADO', 'DEVOLUCION POR ITEM', 'BAJA'])
                    ->groupBy(DB::raw('MONTH(v.created_at)'), DB::raw('UPPER(MONTHNAME(v.created_at))'))
                    ->orderBy('mes')
                    ->get();

        $todos_los_meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
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
            $res_ventas[]   =   round($ventas_por_mes[$mes_num],2);
        }


        //======== COMPRAS ==========
        $compras =   DB::table('compras_grifo as c')
                    ->select(
                        DB::raw('MONTH(c.created_at) as mes'),
                        DB::raw('UPPER(MONTHNAME(c.created_at)) as nombre_mes'),
                        DB::raw('SUM(c.importe_total) as total_mes')
                    )
                    ->where('c.estado','ACTIVO')
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
            $res_compras[]   =   round($compras_por_mes[$mes_num],2);
        }


        $resultados['ventas']       =   $res_ventas;
        $resultados['compras']      =   $res_compras;


        return $resultados;
    }

    public function getAnalisisRentabilidad(string $anio,string $mes):object{
        $facturas               =   $this->getFacturasRentabilidad($anio,$mes);
        $boletas                =   $this->getBoletasRentabilidad($anio,$mes);
        $notas_credito          =   $this->getNotasCreditoRentabilidad($anio,$mes);
        $totales                =   $this->getTotalesRentabilidad($facturas,$boletas,$notas_credito);

        return (object)['datos'=>[$facturas,$boletas,$notas_credito],'totales'=>$totales];
    }

    public function getAnalisisTributario(string $anio,string $mes):object{

        $ventas_tributario      =   $this->getVentasTributario($anio,$mes);
        $compras_tributario     =   $this->getComprasAfectasTributario($anio,$mes);
        $renta                  =   $this->getRentaTributario($ventas_tributario->igv,$compras_tributario->igv,$ventas_tributario->total);

        $data   =   [
                        (object)[
                            'descripcion'       =>  'VALOR VENTA',
                            'ventas'            =>  round($ventas_tributario->subtotal,2),
                            'compras_afectas'   =>  round($compras_tributario->subtotal,2),
                            'compras_inafectas' =>  0
                        ],
                        (object)['descripcion'  => 'IGV',
                            'ventas'            =>  round($ventas_tributario->igv,2),
                            'compras_afectas'   =>  round($compras_tributario->igv,2),
                            'compras_inafectas' =>  0
                        ],
                        (object)[
                            'descripcion'       => 'TOTAL',
                            'ventas'            =>  round($ventas_tributario->total,2),
                            'compras_afectas'   =>  round($compras_tributario->total,2),
                            'compras_inafectas' =>  0
                        ],
                    ];

        $res    =   (object)['data'=>$data,'renta'=>$renta];

        return $res;
    }

    public function getRentaTributario(float $igv_ventas,float $igv_compras,float $total_ventas):object{
        $renta  =   Empresa::find(1)->renta;
        return (object)[
                    'igv_pagar' =>  $igv_ventas - $igv_compras,
                    'renta'     =>  round($total_ventas * $renta,2)
                ];
    }

    public function getVentasTributario(string $anio,string $mes):object{
        $ventas_tributario  =   DB::select('
                                    SELECT
                                    IFNULL(ROUND(SUM(v.subtotal),2),0) as subtotal,
                                    IFNULL(ROUND(SUM(v.igv_monto),2),0) as igv,
                                    IFNULL(ROUND(SUM(v.total),2),0) as total
                                    from ventas_grifo as v
                                    where
                                    v.estado = "ACTIVO"
                                    AND v.estado_sunat NOT IN("ANULADO","DEVOLUCION POR ITEM","BAJA")
                                    AND YEAR(v.created_at) = ?
                                    AND MONTH(v.created_at) = ?
                                ',[$anio,$mes])[0];

        return $ventas_tributario;
    }

    public function getComprasAfectasTributario(string $anio,string $mes):object{
        $compras_tributario  =   DB::selectOne('
                                    SELECT
                                    IFNULL(ROUND(IFNULL(SUM(c.operacion_gravada),0),2),0) as subtotal,
                                    IFNULL(ROUND(IFNULL(SUM(c.monto_igv),0),2),0) as igv,
                                    IFNULL(ROUND(IFNULL(SUM(c.total_pagar),0),2),0) as total
                                    from compras_grifo as c
                                    where
                                    c.estado = "ACTIVO"
                                    AND YEAR(c.created_at) = ?
                                    AND MONTH(c.created_at) = ?
                                ',[$anio,$mes]);

        return $compras_tributario;
    }

    public function getComprasInafectasTributario(string $anio,string $mes):null{
        return null;
    }

    public function getBoletasRentabilidad(string $anio,string $mes):object{

        $boletas_rentabilidad   =   $this->getComprobanteRentabilidad($anio,$mes,'03');
        return $boletas_rentabilidad;

    }

    public function getFacturasRentabilidad(string $anio,string $mes):object{

        $facturas_rentabiidad   =   $this->getComprobanteRentabilidad($anio,$mes,'01');

        return $facturas_rentabiidad;

    }

    public function getNotasCreditoRentabilidad(string $anio,string $mes):object{
        $resultado  =   DB::select('
                        SELECT
                            COUNT(DISTINCT nc.id) as operaciones,
                            SUM(nc.total) as ventas,
                            (
                                SELECT
                                    SUM(IFNULL(round(p.precio_compra,2) * round(ncd.cantidad,2), 0))
                                FROM notas_credito_detalle ncd
                                JOIN productos p ON p.id = ncd.producto_id
                                JOIN notas_credito nc2 ON nc2.id = ncd.nota_credito_id
                                WHERE
                                    YEAR(nc2.created_at) = ?
                                    AND MONTH(nc2.created_at) = ?
                            ) as costos
                        FROM notas_credito as nc
                        WHERE
                            YEAR(nc.created_at) = ?
                            AND MONTH(nc.created_at) = ?',
                        [
                            $anio, $mes,
                            $anio, $mes
                        ]);

        $data   =   $resultado[0];

        return (object)[
            'documento'         => 'NOTAS CRÉDITO',
            'operaciones'       => (int)$data->operaciones,
            'ventas'            => (float)$data->ventas,
            'costos'            => round($data->costos,2),
            'utilidad_bruta'    => round((float)$data->ventas - (float)$data->costos,2)
        ];
    }

    public function getComprobanteRentabilidad(string $anio,string $mes,string $codigo_doc):object{

        $resultado  =   DB::select('
                        SELECT
                            COUNT(DISTINCT v.id) as operaciones,
                            SUM(v.total) as ventas,
                            (
                                SELECT
                                    SUM(IFNULL(round(p.precio_compra,2) * round(vd.cantidad,2), 0))
                                FROM ventas_detalle_grifo vd
                                JOIN productos_grifo p ON p.id = vd.producto_id
                                JOIN ventas_grifo v2 ON v2.id = vd.venta_id
                                WHERE
                                    v2.estado = "ACTIVO"
                                    AND v2.tipo_venta_codigo = ?
                                    AND v2.estado_sunat NOT IN("ANULADO", "BAJA", "DEVOLUCION POR ITEM")
                                    AND YEAR(v2.created_at) = ?
                                    AND MONTH(v2.created_at) = ?
                            ) as costos
                        FROM ventas_grifo as v
                        WHERE
                            v.estado = "ACTIVO"
                            AND v.tipo_venta_codigo = ?
                            AND v.estado_sunat NOT IN("ANULADO", "BAJA", "DEVOLUCION POR ITEM")
                            AND YEAR(v.created_at) = ?
                            AND MONTH(v.created_at) = ?',
                        [
                            $codigo_doc, $anio, $mes,
                            $codigo_doc, $anio, $mes
                        ]);

        $data       =   $resultado[0];
        $documento  =   '';
        if($codigo_doc === '01'){
            $documento  =   'FACTURAS';
        }
        if($codigo_doc === '03'){
            $documento  =   'BOLETAS';
        }

        return (object)[
            'documento'         => $documento,
            'operaciones'       => (int)$data->operaciones,
            'ventas'            => (float)$data->ventas,
            'costos'            => round($data->costos,2),
            'utilidad_bruta'    => round((float)$data->ventas - (float)$data->costos,2)
        ];

    }

    public function getTotalesRentabilidad(object $facturas,object $boletas,object $notas_credito):object{
        return (object)[
            'documento'         =>  'TOTAL',
            'operaciones'       =>  $facturas->operaciones + $boletas->operaciones + $notas_credito->operaciones,
            'ventas'            =>  $facturas->ventas + $boletas->ventas - $notas_credito->ventas,
            'costos'            =>  $facturas->costos + $boletas->costos - $notas_credito->costos,
            'utilidad_bruta'    =>  $facturas->utilidad_bruta + $boletas->utilidad_bruta - $notas_credito->utilidad_bruta
        ];
    }

    public function getAnalisisEficiencia(string $anio,string $mes):object{

        $res    =   (object)[
                        'saldo_cobranza'        =>  $this->getSaldoCobranzaAnt($anio,$mes),
                        'creditos_cobranza'     =>  $this->getCreditosCobranzaMes($anio,$mes),
                        'cobranza'              =>  $this->getCobranzaMes($anio,$mes),
                        'acumulado_cobranza'    =>  $this->getSaldoCobranza($anio,$mes),

                        'saldo_pagar'           =>  $this->getSaldoPagarAnt($anio,$mes),
                        'creditos_pagar'        =>  $this->getCreditosPagarMes($anio,$mes),
                        'pagar'                 =>  $this->getPagarMes($anio,$mes),
                        'acumulado_pagar'       =>  $this->getSaldoPagar($anio,$mes)
                    ];

        return $res;

    }

    public function getCreditosCobranzaMes(string $anio,string $mes):float{

        $res    =   DB::select('SELECT
                    IFNULL(SUM(v.importe_total),0) as creditos
                    FROM ventas_grifo as v
                    WHERE v.estado = "ACTIVO"
                    AND v.estado_sunat NOT IN("ANULADO","DEVOLUCION POR ITEM","BAJA")
                    AND v.condicion_pago_id <> 1
                    AND YEAR(v.created_at) = ?
                    AND MONTH(v.created_at) = ?',[$anio,$mes]);

        return  round($res[0]->creditos,2);

    }

    public function getCreditosPagarMes(string $anio,string $mes):float{

        $res    =   DB::select('SELECT
                    IFNULL(SUM(c.importe_total),0) as creditos
                    FROM compras_grifo as c
                    WHERE c.estado = "ACTIVO"
                    AND c.condicion_pago_id <> 1
                    AND YEAR(c.created_at) = ?
                    AND MONTH(c.created_at) = ?',[$anio,$mes]);

        return  round($res[0]->creditos,2);

    }

    public function getCreditosCobranzaAnt(string $anio,string $mes):float{

        $res    =   DB::select('SELECT
                    IFNULL(SUM(v.importe_total),0) as creditos
                    FROM ventas_grifo as v
                    WHERE v.estado = "ACTIVO"
                    AND v.estado_sunat NOT IN("ANULADO","DEVOLUCION POR ITEM","BAJA")
                    AND v.condicion_pago_id <> 1
                    AND YEAR(v.created_at) <= ?
                    AND MONTH(v.created_at) < ?',[$anio,$mes]);

        return  round($res[0]->creditos,2);

    }

    public function getCreditosPagarAnt(string $anio,string $mes):float{

        $res    =   DB::select('SELECT
                    IFNULL(SUM(c.importe_total),0) as creditos
                    FROM compras_grifo as c
                    WHERE c.estado = "ACTIVO"
                    AND c.condicion_pago_id <> 1
                    AND YEAR(c.created_at) <= ?
                    AND MONTH(c.created_at) < ?',[$anio,$mes]);

        return  round($res[0]->creditos,2);

    }

    public function getCobranzaMes(string $anio,string $mes){

        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.monto),0) as cobranza
                        FROM cuentas_cliente_detalle_grifo AS ccd
                        WHERE
                        ccd.estado <> "ANULADO"
                        AND YEAR(ccd.created_at) = ?
                        AND MONTH(ccd.created_at) = ?',[$anio,$mes]);

        return  round($consulta[0]->cobranza,2);

    }

    public function getPagarMes(string $anio,string $mes){

        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.monto),0) as pagar
                        FROM cuentas_proveedor_detalle_grifo AS ccd
                        WHERE
                        ccd.estado <> "ANULADO"
                        AND YEAR(ccd.created_at) = ?
                        AND MONTH(ccd.created_at) = ?',[$anio,$mes]);

        return  round($consulta[0]->pagar,2);

    }

    public function getCobranzaAnt(string $anio,string $mes){

        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.monto),0) as cobranza
                        FROM cuentas_cliente_detalle_grifo AS ccd
                        WHERE
                        ccd.estado <> "ANULADO"
                        AND YEAR(ccd.created_at) <= ?
                        AND MONTH(ccd.created_at) < ?',[$anio,$mes]);

        return  round($consulta[0]->cobranza,2);

    }

    public function getPagarAnt(string $anio,string $mes){

        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ccd.monto),0) as pagar
                        FROM cuentas_proveedor_detalle_grifo AS ccd
                        WHERE
                        ccd.estado <> "ANULADO"
                        AND YEAR(ccd.created_at) <= ?
                        AND MONTH(ccd.created_at) < ?',[$anio,$mes]);

        return  round($consulta[0]->pagar,2);

    }

    public function getSaldoCobranza(string $anio,string $mes){

        $saldo_ant  =   $this->getSaldoCobranzaAnt($anio,$mes);
        $credito    =   $this->getCreditosCobranzaMes($anio,$mes);
        $cobrado    =   $this->getCobranzaMes($anio,$mes);

        $saldo      =   $saldo_ant + $credito - $cobrado;

        return  round($saldo,2);

    }

    public function getSaldoPagar(string $anio,string $mes){

        $saldo_ant  =   $this->getSaldoPagarAnt($anio,$mes);
        $credito    =   $this->getCreditosPagarMes($anio,$mes);
        $cobrado    =   $this->getPagarMes($anio,$mes);

        $saldo      =   $saldo_ant + $credito - $cobrado;

        return  round($saldo,2);

    }


    public function getSaldoCobranzaAnt(string $anio,string $mes){

        $credito    =   $this->getCreditosCobranzaAnt($anio,$mes);

        $cobrado    =   $this->getCobranzaAnt($anio,$mes);

        return $credito - $cobrado;

    }

    public function getSaldoPagarAnt(string $anio,string $mes){

        $credito    =   $this->getCreditosPagarAnt($anio,$mes);

        $cobrado    =   $this->getPagarAnt($anio,$mes);

        return $credito - $cobrado;

    }

    public function getAnalisisExistencia():object{
        $data_existencias   =   [
                                    'stock_valorizado' =>  $this->getStockValorizado()
                                ];

        return (object)$data_existencias;
    }

    public function getStockValorizado():float{
        $consulta   =   DB::select('SELECT
                        IFNULL(SUM(ap.stock * p.precio_compra),0) as stock_valorizado
                        FROM almacen_productos_grifo as ap
                        JOIN productos_grifo as p on p.id = ap.producto_id
                        WHERE ap.almacen_id = 2
                        AND p.estado = "ACTIVO"');
        return round($consulta[0]->stock_valorizado,2);
    }

}
