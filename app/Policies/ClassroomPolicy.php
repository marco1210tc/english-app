<?php

namespace App\Policies;

use App\Models\Classroom;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ClassroomPolicy
{

    public function manage(User $user, Classroom $classroom): bool
    {
        // Ajusta si tu rol se maneja distinto
        if ($user->role !== 'teacher' && $user->role !== 'admin') return false;

        // admin puede todo
        if ($user->role === 'admin') return true;

        // teacher solo su classroom
        return (int)$classroom->teacher_id === (int)$user->id;
    }
}
