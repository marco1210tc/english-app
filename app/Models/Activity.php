<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'topic_id',
        'name',
        'description',
        'type',
        'content', // El contenido del juego (JSON)
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        // Le dice a Laravel que trate 'content' como un array/objeto
        'content' => 'array', 
    ];

    // --- RELACIONES ---

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function challenges()
    {
        return $this->hasMany(Challenge::class);
    }
}