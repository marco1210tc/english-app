<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'classroom_id',
        'activity_id',
        'title',
        'due_date',
    ];

    protected $casts = [
        'due_date' => 'datetime',
    ];

    // --- RELACIONES ---

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function submissions()
    {
        return $this->hasMany(TaskSubmission::class);
    }
}
