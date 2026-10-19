<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Estado de revisión de los reportes del participante (motor y Au Pair):
 * pendiente → leído (informativo) o solucionado (con mensaje de resolución).
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['program_support_logs', 'au_pair_support_logs'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dateTime('read_at')->nullable()->after('source');
                $t->foreignId('read_by')->nullable()->after('read_at')->constrained('users')->nullOnDelete();
                $t->foreignId('resolved_by')->nullable()->after('resolved_at')->constrained('users')->nullOnDelete();
                $t->index(['source', 'read_at', 'resolved_at']);
            });
        }
    }

    public function down(): void
    {
        foreach (['program_support_logs', 'au_pair_support_logs'] as $table) {
            Schema::table($table, function (Blueprint $t) {
                $t->dropIndex(['source', 'read_at', 'resolved_at']);
                $t->dropConstrainedForeignId('read_by');
                $t->dropConstrainedForeignId('resolved_by');
                $t->dropColumn('read_at');
            });
        }
    }
};
