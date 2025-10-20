<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega campo para guardar la cotización usada en el momento de la transacción
     * Esto asegura que las modificaciones posteriores de exchange_rate no afecten
     * los registros históricos de contabilidad
     */
    public function up(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->decimal('exchange_rate_snapshot', 15, 4)->nullable()->after('amount_pyg')
                ->comment('Cotización de la moneda al momento de la transacción (histórico)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financial_transactions', function (Blueprint $table) {
            $table->dropColumn('exchange_rate_snapshot');
        });
    }
};
