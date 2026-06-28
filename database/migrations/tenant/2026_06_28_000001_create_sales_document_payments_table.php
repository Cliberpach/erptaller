<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PASO 4 - Pago en la venta (Capa A): N líneas de pago por documento de venta.
     * Reemplaza (a futuro) las columnas fijas method_pay_id_1/2 + amount_pay_1/2 de
     * sales_documents. Cada línea = método + cuenta (nullable: NULL=efectivo) + monto
     * + n° operación (opcional). Tabla TENANT.
     */
    public function up(): void
    {
        Schema::create('sales_document_payments', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('sale_document_id');
            $table->foreign('sale_document_id')->references('id')->on('sales_documents')->onDelete('cascade');

            $table->unsignedBigInteger('payment_method_id');
            $table->foreign('payment_method_id')->references('id')->on('payment_methods');

            // NULL = efectivo (caja física, sin cuenta). Si no, la cuenta destino del pago.
            $table->unsignedBigInteger('bank_account_id')->nullable();
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts');

            $table->decimal('amount', 14, 6);
            $table->string('operation_number', 100)->nullable(); // n° operación Yape/transfer (opcional)

            $table->timestamps();

            $table->index('sale_document_id');
            $table->index('payment_method_id');
            $table->index('bank_account_id');
        });

        // ===== BACKFILL: ventas existentes (columnas _1/_2) -> tabla nueva =====
        // 1 fila por _1 (siempre, si method_pay_id_1 no es null) + 1 fila por _2 (si no null).
        // bank_account_id / operation_number quedan NULL (no había ese dato). 0 huérfanos.
        $now   = now();
        $sales = DB::table('sales_documents')
            ->select('id', 'method_pay_id_1', 'amount_pay_1', 'method_pay_id_2', 'amount_pay_2')
            ->get();

        $rows = [];
        foreach ($sales as $s) {
            if (! is_null($s->method_pay_id_1)) {
                $rows[] = [
                    'sale_document_id'  => $s->id,
                    'payment_method_id' => $s->method_pay_id_1,
                    'bank_account_id'   => null,
                    'amount'            => $s->amount_pay_1 ?? 0,
                    'operation_number'  => null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
            if (! is_null($s->method_pay_id_2)) {
                $rows[] = [
                    'sale_document_id'  => $s->id,
                    'payment_method_id' => $s->method_pay_id_2,
                    'bank_account_id'   => null,
                    'amount'            => $s->amount_pay_2 ?? 0,
                    'operation_number'  => null,
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('sales_document_payments')->insert($chunk);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_document_payments');
    }
};
