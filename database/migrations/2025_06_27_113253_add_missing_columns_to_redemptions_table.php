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
        Schema::table('redemptions', function (Blueprint $table) {
            // Add points cost to track how many points were spent
            $table->integer('points_cost')->after('reward_id');
            
            // Add admin notes for when approving/rejecting
            $table->text('admin_notes')->nullable()->after('resolved_at');
            
            // Add delivery tracking information
            $table->string('tracking_number')->nullable()->after('admin_notes');
            $table->string('carrier')->nullable()->after('tracking_number');
            $table->text('delivery_notes')->nullable()->after('carrier');
            $table->timestamp('delivered_at')->nullable()->after('delivery_notes');
            
            // Add timestamps for better tracking
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('redemptions', function (Blueprint $table) {
            $table->dropColumn([
                'points_cost',
                'admin_notes', 
                'tracking_number',
                'carrier',
                'delivery_notes',
                'delivered_at',
                'created_at',
                'updated_at'
            ]);
        });
    }
};
