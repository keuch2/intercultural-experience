<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            // Add category for grouping rewards
            $table->string('category')->default('Otros')->after('cost');
            
            // Add stock management
            $table->integer('stock')->nullable()->after('category');
            
            // Change is_active to status for better control
            $table->enum('status', ['active', 'inactive'])->default('active')->after('stock');
            
            // Add image support
            $table->string('image')->nullable()->after('status');
            
            // Add updated_at timestamp
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });
        
        // Update existing rewards to use new status column
        DB::statement("UPDATE rewards SET status = 'active' WHERE is_active = 1");
        DB::statement("UPDATE rewards SET status = 'inactive' WHERE is_active = 0");
        
        // Remove the old is_active column
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rewards', function (Blueprint $table) {
            // Add back is_active column
            $table->boolean('is_active')->default(true)->after('cost');
        });
        
        // Migrate data back
        DB::statement("UPDATE rewards SET is_active = 1 WHERE status = 'active'");
        DB::statement("UPDATE rewards SET is_active = 0 WHERE status = 'inactive'");
        
        Schema::table('rewards', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'stock', 
                'status',
                'image',
                'updated_at'
            ]);
        });
    }
};
