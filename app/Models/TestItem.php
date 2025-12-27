<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestItem extends Model
{
    protected $table = 'test_items';

    protected $fillable = [
        'test_id', 'item_type_id', 'ref_id', 'order_index'
    ];

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }
}

