<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'user_id',
        'score',
        'status',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // --- RELACIONES ---

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function student()
    {
        // Usamos 'student' como nombre de la relación para claridad
        return $this->belongsTo(User::class, 'user_id');
    }
}
