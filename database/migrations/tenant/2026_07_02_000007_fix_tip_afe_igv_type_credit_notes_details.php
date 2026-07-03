<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * tipAfeIgv es un CÓDIGO de catálogo SUNAT (07: afectación del IGV, ej. "10"), no un monto.
 * Quedó mal tipado como decimal(16,6) -> se guardaba/enviaba "10.000000", que SUNAT rechaza
 * (catálogo 07 no tiene esa clave, solo "10"). sales_documents_details.tip_afe_igv y la
 * referencia (erprestaurante) ya lo tienen como entero — se alinea acá.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE credit_notes_details MODIFY tipAfeIgv BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE credit_notes_details MODIFY tipAfeIgv DECIMAL(16,6) UNSIGNED NOT NULL');
    }
};
