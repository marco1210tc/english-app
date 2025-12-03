<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'order',
        // opcional: 'grade_id' si algún día se requiere módulos por grado
    ];

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order');
    }
}
