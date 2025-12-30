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
        'name',        // "1°A", "2°B"
        'year',        // 2025, etc.
        'description', // opcional
    ];

    public function grade()
    {
        return $this->belongsTo(Grade::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function students()
    {
        // Pivot: classroom_student (classroom_id, student_id)
        return $this->belongsToMany(Student::class)
            ->withTimestamps();
    }
}
