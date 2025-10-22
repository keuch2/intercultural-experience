<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class ProgramHistory extends Model
{
    protected $table = 'program_history';

    protected $fillable = [
        'user_id', 'application_id', 'program_id', 'program_name', 'program_category',
        'started_at', 'completed_at', 'completion_status', 'completion_notes',
        'final_cost', 'final_payment', 'certificate_path', 'certificate_issued_at',
        'is_ie_cue', 'satisfaction_rating', 'testimonial',
    ];

    protected $casts = [
        'started_at' => 'date',
        'completed_at' => 'date',
        'certificate_issued_at' => 'date',
        'final_cost' => 'decimal:2',
        'final_payment' => 'decimal:2',
        'is_ie_cue' => 'boolean',
        'satisfaction_rating' => 'integer',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function application(): BelongsTo { return $this->belongsTo(Application::class); }
    public function program(): BelongsTo { return $this->belongsTo(Program::class); }

    public function getDuration(): ?int {
        if (!$this->started_at || !$this->completed_at) return null;
        return $this->started_at->diffInMonths($this->completed_at);
    }

    public function getCompletionStatusLabel(): string {
        $labels = [
            'completed' => 'Completado',
            'withdrawn' => 'Retirado',
            'terminated' => 'Terminado',
        ];
        return $labels[$this->completion_status] ?? ucfirst($this->completion_status);
    }

    public function scopeIeCue($query) { return $query->where('is_ie_cue', true); }
    public function scopeCompleted($query) { return $query->where('completion_status', 'completed'); }
    public function scopeByUser($query, $userId) { return $query->where('user_id', $userId); }
    public function scopeByProgram($query, $programId) { return $query->where('program_id', $programId); }
}
