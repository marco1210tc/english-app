<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassroomLessonAssignment extends Model
{
    use HasFactory;

    protected $table = 'classroom_lesson_assignments';

    protected $fillable = [
        'classroom_id',
        'lesson_id',
        'assigned_by',
        'status',        // assigned | active | archived
        'assigned_at',   // si tu tabla lo tiene; si no, quítalo
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
