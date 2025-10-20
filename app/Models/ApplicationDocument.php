<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    protected $fillable = [
        'application_id', 'name', 'document_type', 'file_path', 'status', 'observations', 
        'uploaded_at', 'verified_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    protected $hidden = [
        'file_path', 'observations',
    ];

    // Relationships
    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}