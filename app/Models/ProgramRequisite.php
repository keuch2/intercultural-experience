<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgramRequisite extends Model
{
    protected $fillable = [
        'program_id', 'name', 'description', 'type', 'is_required', 'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    /**
     * Tipos de requisitos:
     * - document: Documento que debe ser subido (ej. pasaporte, certificado académico)
     * - action: Acción que debe ser completada (ej. completar formulario, asistir a entrevista)
     * - payment: Pago que debe ser realizado
     */

    // Relationships
    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function userRequisites()
    {
        return $this->hasMany(UserProgramRequisite::class);
    }
}
