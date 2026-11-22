<?php

use App\Models\City;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de ciudades de Paraguay (lista enviada por IE, nov 2026) para que el participante elija su
 * ciudad de una lista (con "Otra" + texto libre) y los datos sirvan para informes y filtros.
 * users.city sigue guardando el nombre (o el texto libre de "Otra"); users.city_id apunta al catálogo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('department', 60);
            $table->unsignedSmallInteger('department_order')->default(0);
            $table->string('country', 60)->default('Paraguay');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['country', 'department', 'name']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('city_id')->nullable()->after('city')->constrained('cities')->nullOnDelete();
        });

        $now = now();
        $rows = [];
        foreach (array_values(City::PARAGUAY) as $order => [$department, $cities]) {
            foreach ($cities as $name) {
                $rows[] = ['name' => $name, 'department' => $department, 'department_order' => $order, 'country' => 'Paraguay', 'is_active' => true, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        DB::table('cities')->insert($rows);

        // Vincula los usuarios que ya tenían la ciudad escrita (sin acentos/mayúsculas/abreviaturas).
        DB::table('users')->whereNotNull('city')->where('city', '!=', '')->orderBy('id')->select(['id', 'city', 'country'])
            ->chunkById(500, function ($users) {
                foreach ($users as $user) {
                    if ($user->country && City::normalize($user->country) !== 'paraguay') {
                        continue;
                    }
                    if ($city = City::matchName($user->city)) {
                        DB::table('users')->where('id', $user->id)->update(['city_id' => $city->id]);
                    }
                }
            });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('city_id');
        });
        Schema::dropIfExists('cities');
    }
};
