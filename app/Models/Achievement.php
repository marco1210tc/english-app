<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon_url',
    ];

    // --- RELACIONES ---

    public function users()
    {
        return $this->belongsToMany(User::class, 'achievement_user')
            ->withPivot('unlocked_at')
            ->using(AchievementUser::class)
            ->as('pivot');
    }
}
