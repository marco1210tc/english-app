<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Hash;


class Student extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'code',
        'pin_hash',
        'classroom_id',
        'avatar',
        'status',
        'first_name',
        'last_name',
    ];

    protected $hidden = ['pin_hash'];

    public function verifyPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    //Relations
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Helpers
    public function activityAttempts()
    {
        return $this->hasMany(StudentActivityAttempt::class);
    }
}
