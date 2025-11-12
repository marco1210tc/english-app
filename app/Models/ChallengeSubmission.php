<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChallengeSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'user_id',
        'score',
        'completed_at',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    // --- RELACIONES ---

    public function challenge()
    {
        return $this->belongsTo(Challenge::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
