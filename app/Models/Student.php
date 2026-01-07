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

    public function initials()
    {
        // Example logic: returns initials from the student's name
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }
}
