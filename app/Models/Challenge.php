<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_id',
        'title',
        'points_reward',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // --- RELACIONES ---

    public function activity()
    {
        return $this->belongsTo(Activity::class);
    }

    public function submissions()
    {
        return $this->hasMany(ChallengeSubmission::class);
    }
}