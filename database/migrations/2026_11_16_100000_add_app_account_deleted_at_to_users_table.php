<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fecha en que el participante eliminó su cuenta de la app: pierde el acceso móvil,
     * pero su ficha, documentos, postulaciones y pagos se conservan en el panel de IE.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('app_account_deleted_at')->nullable()->after('anonymized_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('app_account_deleted_at');
        });
    }
};
