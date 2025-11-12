<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class AchievementUser extends Pivot
{
    // Le decimos que no maneje los created_at/updated_at.
    public $timestamps = false;
}