<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResultItem extends Model
{
    protected $table = 'test_result_items';

    protected $fillable = [
        'test_result_id', 'item_type_id', 'ref_id', 'is_correct', 'response_json', 'time_spent_seconds'
    ];

    protected $casts = [
        'response_json' => 'array',
        'is_correct' => 'boolean',
    ];

    public function testResult()
    {
        return $this->belongsTo(TestResult::class);
    }

    public function itemType()
    {
        return $this->belongsTo(ItemType::class);
    }
}
