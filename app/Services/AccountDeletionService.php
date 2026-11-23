<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ProgramEngine\Notifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Eliminación de la cuenta de la app pedida por el participante (Perfil → "Eliminar mi cuenta").
 *
 * Solo se elimina el acceso a la app: se invalida la contraseña, se cierran las sesiones y se
 * bloquea el ingreso. La ficha del participante, sus documentos, postulaciones, procesos y pagos se
 * conservan en el panel de IE por fines de transparencia, administración y seguimiento.
 * Un admin puede restaurar el acceso: el participante vuelve a crear su contraseña desde la app.
 */
class AccountDeletionService
{
    public function __construct(
        private readonly Notifier $notifier,
    ) {}

    public function closeAppAccount(User $user): void
    {
        DB::transaction(function () use ($user) {
            $user->forceFill([
                'app_account_deleted_at' => now(),
                // La contraseña anterior deja de servir; nadie conoce la nueva.
                'password' => Hash::make(Str::random(64)),
            ])->save();

            $user->tokens()->delete();
            $user->deviceTokens()->delete(); // sin push en los dispositivos donde tenía sesión

            ActivityLog::log('participants')->withAction('app_account_deleted')
                ->withProperties(['user_id' => $user->id])
                ->log("{$user->name} (#{$user->id}) eliminó su cuenta de la app");
        });

        $this->notifier->toAdmins(
            'Cuenta de la app eliminada',
            "{$user->name} ({$user->email}) eliminó su cuenta de la app. Su ficha, documentos, postulaciones y pagos se conservan en el panel; el acceso se puede restaurar desde su ficha.",
            'participants',
        );
    }

    /** El participante vuelve a ingresar creando una contraseña nueva ("Crear contraseña" en la app). */
    public function restoreAppAccount(User $user, ?User $admin = null): void
    {
        $user->forceFill([
            'app_account_deleted_at' => null,
            'requires_password_setup' => true,
        ])->save();

        ActivityLog::log('participants')->withAction('app_account_restored')
            ->withProperties(['user_id' => $user->id, 'admin_id' => $admin?->id])
            ->log("Acceso a la app restaurado para {$user->name} (#{$user->id})");
    }
}
