<?php

namespace App\Http\Services\Tenant\Kardex;

use Illuminate\Support\Facades\DB;

/**
 * Kardex de Cuenta (Paso 4): estado de una cuenta bancaria (ingresos de ventas + egresos),
 * UNION AL VUELO de sales_document_payments + exit_money (sin tabla account_movements).
 * Saldo acumulado calculado: arranca del SALDO DE APERTURA del rango (Σ anteriores a desde),
 * no de 0 -> concilia de verdad con filtro de fechas.
 */
class AccountKardexService
{
    public function kardex(int $bankAccountId, string $desde, string $hasta): array
    {
        // Punto de partida = el saldo GUARDADO de la cuenta (decisión del cliente; simple,
        // es el saldo real que la empresa carga). NO se calcula Σ de movimientos anteriores.
        $saldoBase = (float) (DB::table('bank_accounts')->where('id', $bankAccountId)->value('saldo') ?? 0);

        // Movimientos del rango (entradas de venta + salidas de egreso), ordenados por fecha.
        $movimientos = collect(DB::select($this->sqlMovimientos(), [
            $bankAccountId, $desde, $hasta,   // entradas
            $bankAccountId, $desde, $hasta,   // salidas
        ]));

        // Running sum: la primera fila acumula sobre el saldo de la cuenta.
        $saldo    = $saldoBase;
        $totalIn  = 0.0;
        $totalOut = 0.0;
        $movimientos = $movimientos->map(function ($m) use (&$saldo, &$totalIn, &$totalOut) {
            $saldo    += (float) $m->entrada - (float) $m->salida;
            $m->saldo  = round($saldo, 2);
            $totalIn  += (float) $m->entrada;
            $totalOut += (float) $m->salida;
            return $m;
        });

        return [
            'saldo_cuenta'   => round($saldoBase, 2),
            'movimientos'    => $movimientos->values(),
            'total_ingresos' => round($totalIn, 2),
            'total_egresos'  => round($totalOut, 2),
            'saldo_final'    => round($saldo, 2),
        ];
    }

    private function sqlMovimientos(): string
    {
        return "
            SELECT * FROM (
                SELECT sd.registration_date AS fecha, pm.description AS metodo, 'INGRESO' AS tipo,
                       CONCAT(sd.serie, '-', sd.correlative) AS documento,
                       sdp.amount AS entrada, 0 AS salida, sdp.operation_number AS operacion,
                       sd.user_recorder_name AS registrador, sdp.created_at AS orden
                FROM sales_document_payments sdp
                JOIN sales_documents sd ON sd.id = sdp.sale_document_id
                JOIN payment_methods pm ON pm.id = sdp.payment_method_id
                WHERE sdp.bank_account_id = ? AND sd.status <> 'ANULADO'
                  AND sd.registration_date BETWEEN ? AND ?
                UNION ALL
                SELECT em.date AS fecha, pm.description AS metodo, 'EGRESO' AS tipo,
                       em.number AS documento, 0 AS entrada, em.total AS salida, em.operation_number AS operacion,
                       u.name AS registrador, em.created_at AS orden
                FROM exit_money em
                JOIN payment_methods pm ON pm.id = em.payment_method_id
                JOIN users u ON u.id = em.user_id
                WHERE em.bank_account_id = ? AND em.status = 1
                  AND em.date BETWEEN ? AND ?
            ) mov
            ORDER BY mov.fecha ASC, mov.orden ASC
        ";
    }

}
