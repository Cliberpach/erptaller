<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Placeholder CAJA FICTICIA. La facturación de una OT atribuye la venta a una caja FICTICIO
 * (status ANULADO) para que NO cuente en el cuadre de una caja real (SaleDto::getDtoStoreFromOrder
 * lo lee). Ningún seeder/kit lo creaba -> facturar OT reventaba con "Attempt to read property
 * 'id' on null". Backfill IDEMPOTENTE (existe-o-crea; correrla dos veces no duplica).
 *
 * El petty_cash_book FICTICIO necesita shift_id + user_id (FK). En un tenant ya provisionado
 * existen -> se crea completo. En provisioning fresco (la migración corre ANTES de seedear
 * shifts/users) se crea solo el petty_cash; el kit (CompanyController) crea el book tras los
 * usuarios. Por eso el book se guarda solo si hay shift y user.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $pettyCashId = DB::table('petty_cashes')->where('type', 'FICTICIO')->value('id');
        if (! $pettyCashId) {
            $pettyCashId = DB::table('petty_cashes')->insertGetId([
                'name'       => 'CAJA FICTICIA',
                'type'       => 'FICTICIO',
                'status'     => 'ANULADO',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $shiftId = DB::table('shifts')->value('id');
        $userId  = DB::table('users')->value('id');

        if ($shiftId && $userId && ! DB::table('petty_cash_books')->where('type', 'FICTICIO')->exists()) {
            DB::table('petty_cash_books')->insert([
                'petty_cash_id'   => $pettyCashId,
                'petty_cash_name' => 'CAJA FICTICIA',
                'shift_id'        => $shiftId,
                'user_id'         => $userId,
                'status'          => 'ANULADO',
                'initial_amount'  => 0,
                'initial_date'    => $now,
                'type'            => 'FICTICIO',
            ]);
        }
    }

    public function down(): void
    {
        // No se borra el placeholder en el rollback: ventas-OT ya emitidas podrían
        // referenciarlo (FK petty_cash_book_id). Es un registro de sistema, inocuo.
    }
};
