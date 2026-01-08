<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'lesson_id',
        'title',
        'description',
        'order',
        'config_json',
    ];

    protected $casts = [
        'config_json' => 'array',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order_index');
    }

    public function attempts()
    {
        return $this->hasMany(StudentActivityAttempt::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }

    // Helper para obtener el tipo desde config_json
    public function getTypeAttribute(): ?string
    {
        return $this->config_json['type'] ?? null;
    }
}
