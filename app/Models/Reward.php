<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    // Enable timestamps since we now have both created_at and updated_at
    public $timestamps = true;
    
    protected $fillable = [
        'name', 'description', 'cost', 'category', 'stock', 'status', 'image'
    ];

    protected $casts = [
        'stock' => 'integer',
        'cost' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function redemptions()
    {
        return $this->hasMany(Redemption::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Attributes
    public function getIsAvailableAttribute()
    {
        return $this->status === 'active' && ($this->stock === null || $this->stock > 0);
    }
}