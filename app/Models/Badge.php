<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Badge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',        // único: 'first_activity_completed'
        'description',
        'icon_path',
        'config_json',
        'is_active',
    ];

    protected $casts = [
        'config_json' => 'array',
        'is_active'   => 'boolean',
    ];

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_badges')
            ->withPivot('awarded_at', 'meta_json')
            ->withTimestamps();
    }

    public function studentBadges()
    {
        return $this->hasMany(StudentBadge::class);
    }
}
