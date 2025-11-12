<?php

namespace App\Http\Controllers\Tenant\Consultas;

use App\Exports\CreditosExport;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\NumberToLettersController;
use App\Http\Controllers\Tenant\SaleController;
use App\Models\Company;
use App\Models\PettyCashBook;
use App\Models\Tenant\Credit;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleDetail;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class ConsultasCreditosController extends Controller
{
    public function index()
    {
        return view('consultas.creditos.index');
    }

    public function data(Request $request)
    {
        $credits = DB::table('credits')
            ->select(
                'id',
                'customer_name',
                'customer_document_number',
                'customer_phone',
                'field_name',
                'start_time',
                'end_time',
                'date',
                'total_hours',
                'ball',
                'vest',
                'dni',
                'ruc_number',
                'razon_social',
                'amount',
                'estado',
                'facturado'
            );

        // Filtros
        if ($request->search_type === 'dni') {
            $credits->where('customer_document_number', $request->search_input);
        } elseif ($request->search_type === 'ruc') {
            $credits->where('ruc_number', $request->search_input);
        } elseif ($request->search_type === 'nombre') {
            $credits->where('customer_name', 'LIKE', '%' . $request->search_input . '%');
        } elseif ($request->search_type === 'razon_social') {
            $credits->where('razon_social', 'LIKE', '%' . $request->search_input . '%');
        }


        if ($request->filled('start_date') && $request->filled('end_date')) {
            $credits->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search_estado')) {
            $credits->where('estado', $request->search_estado);
        }

        return DataTables::of($credits)
            ->addColumn('documento', function ($row) use ($request) {
                if ($request->search_type === 'dni' || $request->search_type === 'nombre') {
                    return $row->customer_document_number ?? '-';
                } elseif ($request->search_type === 'ruc' || $request->search_type === 'razon_social') {
                    return $row->ruc_number ?? '-';
                }
                return '-';
            })
            ->addColumn('cliente', function ($row) use ($request) {
                if ($request->search_type === 'nombre' || $request->search_type === 'dni') {
                    return $row->customer_name ?? '-';
                } elseif ($request->search_type === 'razon_social' || $request->search_type === 'ruc') {
                    return $row->razon_social ?? '-';
                }
                return '-';
            })
            ->addColumn('horario', function ($row) {
                return $row->start_time . ' - ' . $row->end_time;
            })
            ->addColumn('ball', fn($row) => $row->ball ? 'Sí' : 'No')
            ->addColumn('vest', fn($row) => $row->vest ? 'Sí' : 'No')
            ->addColumn('dni', fn($row) => $row->dni ? 'Sí' : 'No')
            ->addColumn('facturado', fn($row) => $row->facturado ? 'Sí' : 'No')
            ->make(true);
    }

    public function exportExcel(Request $request)
    {
        $filters = [
            'search_type' => $request->query('search_type'),
            'search_input' => $request->query('search_input'),
            'start_date' => $request->query('start_date'),
            'end_date' => $request->query('end_date'),
            'search_estado' => $request->query('search_estado'),
        ];
        return Excel::download(new CreditosExport($filters), 'creditos.xlsx');
    }



    public function generateCreditPDF(Request $request)
    {
        $searchType = $request->query('search_type');
        $searchInput = $request->query('search_input');
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $estado = $request->query('search_estado', 'PENDIENTE');

        $credits = DB::table('credits')
            ->select(
                'customer_name',
                'customer_document_number',
                'customer_phone',
                'field_name',
                'start_time',
                'end_time',
                'date',
                'total_hours',
                'ball',
                'vest',
                'dni',
                'ruc_number',
                'razon_social',
                'amount'
            )
            ->where('estado', $estado);

        if ($searchType === 'dni') {
            $credits->where('customer_document_number', $searchInput);
        } elseif ($searchType === 'ruc') {
            $credits->where('ruc_number', $searchInput);
        } elseif ($searchType === 'nombre') {
            $credits->where(function ($query) use ($searchInput) {
                $query->where('customer_name', 'like', '%' . $searchInput . '%')
                    ->orWhere('razon_social', 'like', '%' . $searchInput . '%');
            });
        }

        if ($startDate && $endDate) {
            $credits->whereBetween('date', [$startDate, $endDate]);
        }

        $credits = $credits->get();

        $company = Company::first();

        $pdf = PDF::loadView('consultas.creditos.pdf.creditos', [
            'credits' => $credits,
            'company' => $company,
            'search_type' => $searchType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'search_estado' => $estado
        ]);

        return $pdf->stream('creditos_pendientes.pdf');
    }


    public function generarDocumento(Request $request)
    {
        try {
            DB::beginTransaction();

            $creditos = $request->creditos;
            $tipo_comprobante = $request->tipo_comprobante; // 'boleta' o 'factura'
            $documento = $request->documento;

            // Validar cliente según DNI o RUC
            $customer = $this->obtenerClientePorDocumento($tipo_comprobante, $documento);
            if (!$customer) {
                throw new \Exception("Cliente no encontrado.");
            }

            // Recolectar créditos
            $creditosDB = DB::table('credits')->whereIn('id', $creditos)->get();
            if ($creditosDB->isEmpty()) {
                throw new \Exception("No se encontraron créditos válidos.");
            }

            // Obtener IGV configurado en la empresa
            $company = Company::find(1);
            $igv = $company ? $company->igv : 18;

            $total = $creditosDB->sum('amount');
            $subtotal = $total / (1 + ($igv / 100));
            $igv_amount = $total - $subtotal;
            $legend = NumberToLettersController::numberToLetters($total);

            // Obtener serie y correlativo
            $type_sale_code = ($tipo_comprobante === 'boleta') ? '3' : '1';
            $type_sale_name = ($tipo_comprobante === 'boleta') ? 'BOLETA DE VENTA ELECTRÓNICA' : 'FACTURA ELECTRÓNICA';

            $data_correlative = SaleController::getCorrelative($type_sale_code);
            if (!$data_correlative) {
                throw new \Exception("No se pudo obtener el correlativo para la venta.");
            }

            // Crear la venta
            $sale = new Sale();
            $sale->customer_id = $customer->id;
            $sale->customer_name = $customer->name;
            $sale->customer_type_document = $customer->type_document_abbreviation;
            $sale->customer_document_number = $customer->document_number;
            $sale->customer_document_code = $customer->type_document_code;
            $sale->customer_phone = $customer->phone;
            $sale->user_recorder_id = auth()->id();
            $sale->user_recorder_name = auth()->user()->name;
            $sale->petty_cash_id = 1;
            $sale->petty_cash_name = 'CAJA PRINCIPAL';
            $sale->petty_cash_book_id = 1;
            $sale->type_sale_code = $type_sale_code;
            $sale->type_sale_name = $type_sale_name;
            $sale->igv_percentage = $igv;
            $sale->subtotal = round($subtotal, 2);
            $sale->igv_amount = round($igv_amount, 2);
            $sale->total = round($total, 2);
            $sale->legend = $legend;
            $sale->method_pay_id_1 = 1; // Efectivo
            $sale->amount_pay_1 = round($total, 2);
            $sale->correlative = $data_correlative->correlative;
            $sale->serie = $data_correlative->serie;
            $sale->save();

            // Inicializar caja abierta
            $cajaAbierta = PettyCashBook::where('status', 'open')->first();
            if ($cajaAbierta && is_null($cajaAbierta->closing_amount)) {
                $cajaAbierta->closing_amount = $cajaAbierta->initial_amount;
            }

            // Agrupar créditos por nombre y monto
            $grupos = [];

            foreach ($creditosDB as $credito) {
                $clave = ($credito->customer_name ?? $credito->razon_social) . '|' . $credito->amount;

                if (!isset($grupos[$clave])) {
                    $grupos[$clave] = [
                        'descripcion' => 'Pago de crédito - ' . ($credito->customer_name ?? $credito->razon_social ?? 'Desconocido'),
                        'cantidad' => 1,
                        'monto' => $credito->amount,
                        'ids' => [$credito->id],
                        'booking_ids' => [$credito->booking_id]
                    ];
                } else {
                    $grupos[$clave]['cantidad'] += 1;
                    $grupos[$clave]['ids'][] = $credito->id;
                    $grupos[$clave]['booking_ids'][] = $credito->booking_id;
                }
            }

            // Registrar los detalles de venta agrupados
            foreach ($grupos as $grupo) {
                $totalGrupo = $grupo['cantidad'] * $grupo['monto'];
                $base_igv = $totalGrupo / (1 + ($igv / 100));
                $impuesto = $totalGrupo - $base_igv;

                SaleDetail::create([
                    'sale_document_id'     => $sale->id,
                    'product_id'           => 9999,
                    'product_code'         => 'CRED-GROUP',
                    'product_unit'         => 'NIU',
                    'product_description'  => $grupo['descripcion'],
                    'product_name'         => 'Pago crédito',
                    'category_name'        => 'CRÉDITOS',
                    'brand_name'           => '-',
                    'quantity'             => $grupo['cantidad'],
                    'price_sale'           => round($grupo['monto'], 2),
                    'amount'               => round($totalGrupo, 2),
                    'mto_valor_unitario'   => round($base_igv / $grupo['cantidad'], 2),
                    'mto_valor_venta'      => round($base_igv, 2),
                    'mto_base_igv'         => round($base_igv, 2),
                    'porcentaje_igv'       => round($igv, 2),
                    'igv'                  => round($impuesto, 2),
                    'tip_afe_igv'          => 10,
                    'total_impuestos'      => round($impuesto, 2),
                    'mto_precio_unitario'  => round($grupo['monto'], 2)
                ]);

                // Marcar créditos como pagados
                DB::table('credits')->whereIn('id', $grupo['ids'])->update([
                    'estado' => 'PAGADO',
                    'facturado' => 1,
                    'updated_at' => now()
                ]);

                // Actualizar bookings relacionados
                DB::table('bookings')->whereIn('id', array_filter($grupo['booking_ids']))->update([
                    'payment_status' => 'TOTAL'
                ]);

                // Sumar a caja
                if ($cajaAbierta) {
                    $cajaAbierta->closing_amount += $totalGrupo;
                    $cajaAbierta->sale_day += $totalGrupo;
                }
            }


            DB::commit();

            $url_pdf = route('tenant.ventas.comprobante_venta.pdf_voucher', ['id' => $sale->id]);

            return response()->json([
                'success' => true,
                'message' => 'Documento generado correctamente.',
                'url_pdf' => $url_pdf
            ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $th->getMessage()
            ]);
        }
    }


    private function obtenerClientePorDocumento($tipo, $numero)
    {
        $tipoAbrev = $tipo === 'boleta' ? 'DNI' : 'RUC';
        $isBoleta  = $tipo === 'boleta';
    
        $query = DB::connection('landlord')->table('customers');
    
        // Buscar por documento según tipo
        if ($isBoleta) {
            $cliente = $query
                ->where('document_number', $numero)
                ->where('type_document_abbreviation', 'DNI')
                ->first();
        } else {
            $cliente = $query
                ->where('ruc_number', $numero)
                ->where('type_document_abbreviation', 'RUC')
                ->first();
        }
    
        // Si ya existe, retornarlo
        if ($cliente) {
            return $cliente;
        }
    
        // Si no existe, crear con los datos del input
        $nombreInput = request('nombre_razon_social');
        $telefono    = request('telefono') ?? null;
    
        $id = DB::connection('landlord')->table('customers')->insertGetId([
            'document_number'             => $numero,
            'ruc_number'                  => $isBoleta ? null : $numero,
            'name'                        => $nombreInput,
            'razon_social'               => $isBoleta ? null : $nombreInput,
            'phone'                       => $telefono,
            'type_identity_document_id'  => $isBoleta ? 1 : 3,
            'type_document_name'         => $isBoleta ? 'DOCUMENTO NACIONAL DE IDENTIDAD' : 'REGISTRO ÚNICO DE CONTRIBUYENTE',
            'type_document_abbreviation' => $tipoAbrev,
            'type_document_code'         => $isBoleta ? '01' : '06',
            'address'                    => null,
            'email'                      => null,
            'department_id'              => null,
            'province_id'                => null,
            'district_id'                => null,
            'department_name'           => null,
            'province_name'             => null,
            'district_name'             => null,
            'zone'                       => null,
            'ubigeo'                     => null,
            'created_at'                 => now(),
            'updated_at'                 => now(),
        ]);
    
        return DB::connection('landlord')->table('customers')->where('id', $id)->first();
    }
    
}
