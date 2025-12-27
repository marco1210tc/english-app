<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemType extends Model
{
    protected $table = 'item_types';

    protected $fillable = [
        'key',
        'name',
        'description',
        'is_active',
    ];

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function testItems()
    {
        return $this->hasMany(TestItem::class);
    }

    public function testResultItems()
    {
        return $this->hasMany(TestResultItem::class);
    }
}
