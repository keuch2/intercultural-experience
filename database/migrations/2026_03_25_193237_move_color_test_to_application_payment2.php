<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Módulo 11: Move Color Test from "Documentos - 1er Pago" (application_payment1)
 * to "Documentos - 2do Pago" (application_payment2).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('au_pair_documents')
            ->where('document_type', 'color_test')
            ->where('stage', 'application_payment1')
            ->update([
                'stage' => 'application_payment2',
                'sort_order' => 29,
            ]);
    }

    public function down(): void
    {
        DB::table('au_pair_documents')
            ->where('document_type', 'color_test')
            ->where('stage', 'application_payment2')
            ->update([
                'stage' => 'application_payment1',
                'sort_order' => 22,
            ]);
    }
};
