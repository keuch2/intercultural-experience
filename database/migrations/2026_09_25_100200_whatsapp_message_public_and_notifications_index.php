<?php

use App\Models\SystemSetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Mensaje de WhatsApp editable desde el admin y visible para la app (placeholders {nombre} y {programa})
        $existing = SystemSetting::where('key', 'whatsapp_welcome_message')->first();
        if ($existing) {
            $existing->update(['is_public' => true, 'description' => 'Mensaje inicial del participante por WhatsApp (placeholders {nombre} y {programa})']);
        } else {
            SystemSetting::set('whatsapp_welcome_message', 'Hola, soy {nombre}, participante del programa {programa} de Intercultural Experience. Necesito ayuda con mi proceso.', 'string', 'whatsapp', 'Mensaje inicial del participante por WhatsApp (placeholders {nombre} y {programa})', true);
        }
        Cache::forget('system_setting_whatsapp_welcome_message');

        // Contador de no leídos por usuario
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['user_id', 'is_read'], 'notifications_user_read_idx');
        });
    }

    public function down(): void
    {
        Schema::table('notifications', fn (Blueprint $table) => $table->dropIndex('notifications_user_read_idx'));
    }
};
