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
        Schema::table('programs', function (Blueprint $table) {
            $table->enum('main_category', ['IE', 'YFU'])->after('category');
            $table->string('subcategory', 100)->nullable()->after('main_category');
            
            // Renombrar category actual a subcategory para migración de datos
            $table->renameColumn('category', 'old_category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table) {
            $table->renameColumn('old_category', 'category');
            $table->dropColumn(['main_category', 'subcategory']);
        });
    }
};
