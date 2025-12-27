<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TestResult extends Model
{
    protected $table = 'test_results';

    protected $fillable = [
        'test_id', 'student_id', 'score', 'started_at', 'finished_at'
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(TestResultItem::class, 'test_result_id');
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }
}
