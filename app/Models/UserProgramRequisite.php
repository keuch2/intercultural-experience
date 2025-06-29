<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProgramRequisite extends Model
{
    protected $fillable = [
        'application_id', 'program_requisite_id', 'status', 'file_path', 
        'observations', 'completed_at', 'verified_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Estados posibles:
     * - pending: Pendiente de completar por el usuario
     * - completed: Completado por el usuario, pendiente de verificación
     * - verified: Verificado por el administrador
     * - rejected: Rechazado por el administrador
     */

    // Relationships
    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function programRequisite()
    {
        return $this->belongsTo(ProgramRequisite::class);
    }
}
