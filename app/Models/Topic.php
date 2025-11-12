<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'icon_url',
    ];

    // --- RELACIONES ---

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }
}
