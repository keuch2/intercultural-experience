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
        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            
            // Información de la empresa
            $table->string('company_name');
            $table->string('business_type');
            $table->string('ein_number')->nullable(); // Employer Identification Number
            $table->year('established_year')->nullable();
            $table->integer('number_of_employees')->nullable();
            
            // Ubicación
            $table->string('address');
            $table->string('city');
            $table->string('state', 2);
            $table->string('zip_code', 10);
            $table->string('country')->default('USA');
            $table->string('phone');
            $table->string('email');
            $table->string('website')->nullable();
            
            // Contacto principal
            $table->string('contact_name');
            $table->string('contact_title');
            $table->string('contact_phone');
            $table->string('contact_email');
            
            // Información del programa
            $table->integer('years_in_program')->default(0);
            $table->integer('participants_hired_total')->default(0);
            $table->integer('participants_hired_this_year')->default(0);
            $table->integer('positions_available')->default(0);
            $table->json('job_categories')->nullable(); // Array de categorías de trabajo
            $table->json('seasons_hiring')->nullable(); // ['summer', 'winter']
            
            // Validación y estado
            $table->boolean('is_verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            
            // Rating y feedback
            $table->decimal('rating', 3, 2)->nullable(); // 0.00 - 5.00
            $table->integer('total_reviews')->default(0);
            $table->text('notes')->nullable();
            
            // Documentos
            $table->string('business_license_path')->nullable();
            $table->string('insurance_certificate_path')->nullable();
            $table->string('agreement_path')->nullable();
            
            // Compliance
            $table->boolean('e_verify_enrolled')->default(false);
            $table->boolean('workers_comp_insurance')->default(false);
            $table->boolean('liability_insurance')->default(false);
            $table->date('last_audit_date')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('company_name');
            $table->index(['state', 'is_active']);
            $table->index('is_verified');
            $table->index('rating');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employers');
    }
};
