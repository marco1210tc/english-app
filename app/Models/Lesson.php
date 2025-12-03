<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'order',
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class)->orderBy('order');
    }
}
