<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('participant_notes', function (Blueprint $table) {
            $table->foreignId('application_id')
                ->nullable()
                ->after('admin_id')
                ->constrained('applications')
                ->nullOnDelete();
            $table->index('application_id');
        });

        // Backfill: vincular cada nota existente a la Application más antigua del usuario.
        // Notas de usuarios sin aplicaciones quedan con application_id = NULL (huérfanas).
        $backfilled = 0;
        $orphans = 0;
        $notes = DB::table('participant_notes')->whereNull('application_id')->get();

        foreach ($notes as $note) {
            $firstAppId = DB::table('applications')
                ->where('user_id', $note->user_id)
                ->orderBy('created_at')
                ->value('id');

            if ($firstAppId) {
                DB::table('participant_notes')
                    ->where('id', $note->id)
                    ->update(['application_id' => $firstAppId]);
                $backfilled++;
            } else {
                $orphans++;
            }
        }

        // Log resultado en stdout para auditoría del deploy
        if ($backfilled > 0 || $orphans > 0) {
            echo "  participant_notes backfill: {$backfilled} vinculadas, {$orphans} huérfanas (sin Application)\n";
        }
    }

    public function down(): void
    {
        Schema::table('participant_notes', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropIndex(['application_id']);
            $table->dropColumn('application_id');
        });
    }
};
