<?php

namespace App\Livewire\Teacher\Classrooms;

use Livewire\Component;
use App\Models\Classroom;

class Index extends Component
{
    public $classrooms = [];

    public function mount(): void
    {
        $q = Classroom::query()->with('grade:id,name');

        if (auth()->user()->role === 'teacher') {
            $q->where('teacher_id', auth()->id());
        }

        $this->classrooms = $q->orderBy('grade_id')->orderBy('name')->get();
    }

    public function render()
    {
        return view('livewire.teacher.classrooms.index');
    }
}
