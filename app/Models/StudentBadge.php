<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StudentBadge extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'badge_id',
        'awarded_at',
        'meta_json',
    ];

    protected $casts = [
        'awarded_at' => 'datetime',
        'meta_json'  => 'array',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function badge()
    {
        return $this->belongsTo(Badge::class);
    }
}
