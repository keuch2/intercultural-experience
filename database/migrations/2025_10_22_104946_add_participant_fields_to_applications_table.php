<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            // Datos personales básicos
            $table->string('full_name')->after('user_id')->nullable();
            $table->date('birth_date')->after('full_name')->nullable();
            $table->string('cedula')->after('birth_date')->nullable();
            $table->string('passport_number')->after('cedula')->nullable();
            $table->date('passport_expiry')->after('passport_number')->nullable();
            $table->string('phone')->after('passport_expiry')->nullable();
            $table->string('address')->after('phone')->nullable();
            $table->string('city')->after('address')->nullable();
            $table->string('country')->after('city')->default('Paraguay');
            
            // Estado y progreso
            $table->string('current_stage')->after('status')->nullable()->comment('Etapa actual del proceso');
            $table->integer('progress_percentage')->after('current_stage')->default(0);
            
            // Datos financieros
            $table->decimal('total_cost', 10, 2)->after('progress_percentage')->nullable();
            $table->decimal('amount_paid', 10, 2)->after('total_cost')->default(0);
            
            // Fechas importantes
            $table->timestamp('started_at')->after('applied_at')->nullable();
            
            // Índices
            $table->index('cedula');
            $table->index('passport_number');
            $table->index('current_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropIndex(['cedula']);
            $table->dropIndex(['passport_number']);
            $table->dropIndex(['current_stage']);
            
            $table->dropColumn([
                'full_name',
                'birth_date',
                'cedula',
                'passport_number',
                'passport_expiry',
                'phone',
                'address',
                'city',
                'country',
                'current_stage',
                'progress_percentage',
                'total_cost',
                'amount_paid',
                'started_at',
            ]);
        });
    }
};
