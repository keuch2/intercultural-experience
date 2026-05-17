<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Postulantes cargados por admin desde el backoffice no tienen contraseña real
 * (admin pone un placeholder). Cuando bajan la app, deben generar la suya en su
 * primer ingreso. Este flag marca a esos usuarios y se apaga cuando setean su
 * contraseña vía /api/password/setup.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'requires_password_setup')) {
                $table->boolean('requires_password_setup')->default(false)->after('password');
            }
            if (! Schema::hasColumn('users', 'password_set_at')) {
                $table->timestamp('password_set_at')->nullable()->after('requires_password_setup');
            }
        });

        // Para usuarios existentes que se registraron vía app o web "normal",
        // marcamos password_set_at = created_at. Para postulantes cargados por
        // admin (role=user sin password_set_at), el admin decidirá si los marca
        // requires_password_setup=true manualmente o por seeder posterior.
        DB::table('users')
            ->whereNull('password_set_at')
            ->whereNotNull('password')
            ->update([
                'password_set_at' => DB::raw('created_at'),
            ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_set_at')) {
                $table->dropColumn('password_set_at');
            }
            if (Schema::hasColumn('users', 'requires_password_setup')) {
                $table->dropColumn('requires_password_setup');
            }
        });
    }
};
