<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuPairResource extends Model
{
    protected $fillable = [
        'title',
        'description',
        'icon',
        'file_type',
        'file_path',
        'original_filename',
        'file_size',
        'external_url',
        'is_active',
        'sort_order',
        'uploaded_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'file_size' => 'integer',
        'sort_order' => 'integer',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        if (!$bytes) return '-';
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->hasFile() ? 'Disponible' : 'Pendiente de carga';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->hasFile() ? 'success' : 'secondary';
    }

    public function getFileTypeBadgeColorAttribute(): string
    {
        return match(strtoupper($this->file_type)) {
            'PDF' => 'danger',
            'DOC', 'DOCX' => 'primary',
            'VIDEO' => 'info',
            'LINK' => 'warning',
            default => 'secondary',
        };
    }
}
