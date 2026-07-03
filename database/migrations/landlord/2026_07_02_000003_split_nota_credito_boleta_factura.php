<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Separa "NOTA DE CRÉDITO ELECTRÓNICA" (parameter FC, un solo tipo para boleta y factura)
 * en dos tipos de comprobante: NC de BOLETA (parameter BB) y NC de FACTURA (parameter FF).
 * Convención SUNAT: la serie de la NC dobla la letra del comprobante afectado (B->BB, F->FF).
 *
 * El tipo viejo (FC) se ANULA, no se borra: preserva el historial de document_serializations
 * / credit_notes ya emitidas con esa serie.
 */
return new class extends Migration
{
    private const GT_COMPROBANTES = 4;

    public function up(): void
    {
        $db = DB::connection('landlord');

        // En migrate:fresh, general_tables aun no tiene datos (los siembra el seeder despues).
        // Este parche es solo para BD existentes con el registro COMPROBANTES (id=4) ya creado.
        if (!$db->table('general_tables')->where('id', self::GT_COMPROBANTES)->exists()) {
            return;
        }

        if (!$db->table('general_table_details')->where('general_table_id', self::GT_COMPROBANTES)->where('parameter', 'BB')->exists()) {
            $db->table('general_table_details')->insert([
                'general_table_id' => self::GT_COMPROBANTES,
                'name'             => 'NOTA DE CRÉDITO ELECTRÓNICA - BOLETA',
                'description'      => 'NOTA DE CRÉDITO ELECTRÓNICA - BOLETA',
                'symbol'           => '07',
                'parameter'        => 'BB',
                'status'           => 'ACTIVO',
                'editable'         => false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        if (!$db->table('general_table_details')->where('general_table_id', self::GT_COMPROBANTES)->where('parameter', 'FF')->exists()) {
            $db->table('general_table_details')->insert([
                'general_table_id' => self::GT_COMPROBANTES,
                'name'             => 'NOTA DE CRÉDITO ELECTRÓNICA - FACTURA',
                'description'      => 'NOTA DE CRÉDITO ELECTRÓNICA - FACTURA',
                'symbol'           => '07',
                'parameter'        => 'FF',
                'status'           => 'ACTIVO',
                'editable'         => false,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }

        $db->table('general_table_details')
            ->where('general_table_id', self::GT_COMPROBANTES)
            ->where('parameter', 'FC')
            ->update(['status' => 'ANULADO']);
    }

    public function down(): void
    {
        $db = DB::connection('landlord');

        $db->table('general_table_details')
            ->where('general_table_id', self::GT_COMPROBANTES)
            ->whereIn('parameter', ['BB', 'FF'])
            ->delete();

        $db->table('general_table_details')
            ->where('general_table_id', self::GT_COMPROBANTES)
            ->where('parameter', 'FC')
            ->update(['status' => 'ACTIVO']);
    }
};
