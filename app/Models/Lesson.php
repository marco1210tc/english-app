<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'order_index',
        'estimated_time',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // --- Relaciones base ---
    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    // Si necesitas el grade desde Lesson:
    public function grade()
    {
        return $this->module?->grade(); // acceso por relación (no query)
    }

    // --- Actividades ---
    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    // --- Vocabulario de lección ---
    public function vocabulary()
    {
        return $this->belongsToMany(Vocabulary::class, 'lesson_vocabulary')
            ->withPivot(['order_index']);
    }

    // --- Asignación a aulas (secciones) ---
    public function classroomAssignments()
    {
        return $this->hasMany(ClassroomLessonAssignment::class);
    }

    public function classrooms()
    {
        return $this->belongsToMany(Classroom::class, 'classroom_lesson_assignments')
            ->withPivot(['status', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    // --- Scopes útiles ---
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
