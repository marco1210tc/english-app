<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Test extends Model
{
    protected $table = 'tests';

    protected $fillable = [
        'grade_id', 'type', 'title', 'created_by'
    ];

    public function items()
    {
        return $this->hasMany(TestItem::class, 'test_id');
    }
}
