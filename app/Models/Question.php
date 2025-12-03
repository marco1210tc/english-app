<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'text',
        'audio_path',   // para listen & repeat, opcional
        'image_path',   // si se requiere
        'order',
        'meta_json',    // pista extra, etc.
    ];

    protected $casts = [
        'meta_json' => 'array',
    ];

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function options()
    {
        return $this->hasMany(QuestionOption::class);
    }
}
