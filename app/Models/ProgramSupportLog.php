<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgramSupportLog extends Model
{
    protected $fillable = [
        'program_process_id', 'log_type', 'title', 'description', 'log_date', 'follow_up_number',
        'severity', 'resolution', 'resolved_at', 'logged_by',
    ];

    protected $casts = [
        'log_date' => 'date',
        'follow_up_number' => 'integer',
        'resolved_at' => 'datetime',
    ];

    public function process(): BelongsTo
    {
        return $this->belongsTo(ProgramProcess::class, 'program_process_id');
    }

    public function loggedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by');
    }
}
