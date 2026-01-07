<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classroom extends Model
{
    use HasFactory;

    protected $fillable = [
        'grade_id',
        'teacher_id',
        'name',        // ej: "A", "B" o "1A"
        'class_code',  // ej: "1A"
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // --- Relaciones base ---
    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    // --- Asignación de lecciones (MVP) ---
    public function lessonAssignments()
    {
        return $this->hasMany(ClassroomLessonAssignment::class);
    }

    public function lessons()
    {
        return $this->belongsToMany(Lesson::class, 'classroom_lesson_assignments')
            ->withPivot(['status', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    // --- Scopes útiles ---
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
