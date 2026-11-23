<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pedido de IE (nov 2026): formulario de información personal obligatorio en Admisión.
 * El participante lo confirma una vez por postulación (cada programa nuevo vuelve a pedirlo); se guarda en
 * `applications` para que sirva igual en el motor y en Au Pair, y aun antes de que IE apruebe la solicitud.
 * La exigencia se activa desde Configuración (`personal_info_required`) cuando la app nueva esté en tiendas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $t) {
            $t->timestamp('personal_info_confirmed_at')->nullable();
            $t->foreignId('personal_info_confirmed_by')->nullable()->constrained('users')->nullOnDelete();
        });

        // Guard en la etapa de Admisión de los programas del motor.
        $stages = DB::table('program_stages')
            ->join('programs', 'programs.id', '=', 'program_stages.program_id')
            ->where('programs.engine_enabled', true)
            ->where('program_stages.key', 'admission')
            ->get(['program_stages.id', 'program_stages.guards']);
        foreach ($stages as $stage) {
            $guards = json_decode((string) $stage->guards, true) ?: [];
            $guards['require_personal_info'] = true;
            DB::table('program_stages')->where('id', $stage->id)->update(['guards' => json_encode($guards)]);
        }
    }

    public function down(): void
    {
        foreach (DB::table('program_stages')->whereNotNull('guards')->get(['id', 'guards']) as $stage) {
            $guards = json_decode((string) $stage->guards, true) ?: [];
            if (array_key_exists('require_personal_info', $guards)) {
                unset($guards['require_personal_info']);
                DB::table('program_stages')->where('id', $stage->id)->update(['guards' => json_encode($guards)]);
            }
        }

        Schema::table('applications', function (Blueprint $t) {
            $t->dropConstrainedForeignId('personal_info_confirmed_by');
            $t->dropColumn('personal_info_confirmed_at');
        });
    }
};
