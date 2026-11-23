<?php

use App\Support\NotificationCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Fase 6 (nov 2026): notificaciones push (Expo) + preferencias por categoría + avisos especiales (campañas).
 * Todo aviso sigue quedando en `notifications` (in-app); estas tablas solo agregan la push.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_types', function (Blueprint $table) {
            $table->string('key', 60)->primary();
            $table->boolean('push_enabled')->default(true);
            $table->boolean('mandatory')->default(false);
            $table->timestamps();
        });

        $now = now();
        DB::table('notification_types')->insert(collect(NotificationCatalog::TYPES)->map(fn ($t, $key) => [
            'key' => $key, 'push_enabled' => true, 'mandatory' => (bool) $t[2], 'created_at' => $now, 'updated_at' => $now,
        ])->values()->all());

        Schema::create('notification_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title', 120);
            $table->text('message');
            $table->string('link_type', 20)->default('none'); // none|screen|program|url
            $table->string('link_target', 30)->nullable();
            $table->foreignId('link_program_id')->nullable()->constrained('programs')->nullOnDelete();
            $table->string('link_url', 500)->nullable();
            $table->json('audience');
            $table->string('channel', 10)->default('push'); // in_app|push
            $table->boolean('is_important')->default(false);
            $table->timestamp('scheduled_at')->nullable();
            $table->string('status', 20)->default('scheduled'); // scheduled|sending|sent|cancelled
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->string('notification_type', 60)->nullable()->after('category');
            $table->json('data')->nullable()->after('notification_type');
            $table->foreignId('campaign_id')->nullable()->after('data')->constrained('notification_campaigns')->nullOnDelete();
            // null = sin push · pending · sending · sent · failed · no_device · muted · disabled · expired
            $table->string('push_status', 20)->nullable()->after('campaign_id');
            $table->timestamp('pushed_at')->nullable()->after('push_status');
            $table->index('push_status');
            $table->index('notification_type');
        });

        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('category', 30);
            $table->boolean('push_enabled')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'category']);
        });

        Schema::create('device_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('platform', 10)->nullable();
            $table->string('app_version', 20)->nullable();
            $table->string('device_name', 100)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('push_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained()->cascadeOnDelete();
            $table->foreignId('device_token_id')->nullable()->constrained()->nullOnDelete();
            $table->string('ticket_id', 64)->nullable();
            $table->string('status', 10); // ok|error
            $table->string('error', 60)->nullable();
            $table->string('error_message', 255)->nullable();
            $table->string('receipt_status', 10)->nullable(); // ok|error|unknown
            $table->string('receipt_error', 60)->nullable();
            $table->timestamp('receipt_checked_at')->nullable();
            $table->timestamps();
            $table->index(['receipt_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_deliveries');
        Schema::dropIfExists('device_tokens');
        Schema::dropIfExists('user_notification_preferences');
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropIndex(['push_status']);
            $table->dropIndex(['notification_type']);
            $table->dropColumn(['notification_type', 'data', 'campaign_id', 'push_status', 'pushed_at']);
        });
        Schema::dropIfExists('notification_campaigns');
        Schema::dropIfExists('notification_types');
    }
};
