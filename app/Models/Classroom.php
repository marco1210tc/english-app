<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'name',
        'grade_level',
    ];

    // --- RELACIONES ---

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        // Un aula tiene muchos estudiantes
        return $this->belongsToMany(User::class, 'classroom_user');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
