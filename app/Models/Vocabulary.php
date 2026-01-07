<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vocabulary extends Model
{
    use HasFactory;

    protected $table = 'vocabulary';

    protected $fillable = [
        'word_en',
        'translation_es',
        'image_path',
        'audio_path',
        'status',      // draft | published
        'created_by',  // user id
    ];

    protected $casts = [
        'created_by' => 'integer',
    ];

    // --- Relaciones ---

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'lesson_vocabulary')
            ->withPivot(['order_index']);
    }

    // --- Scopes útiles ---

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }
}
