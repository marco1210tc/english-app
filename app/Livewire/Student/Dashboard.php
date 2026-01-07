<?php

namespace App\Livewire\Student;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Dashboard extends Component
{
    public $lessons = [];

    public function mount(): void
    {
        $student = auth('student')->user();

        $this->lessons = DB::table('classroom_lesson_assignments as cla')
            ->join('lessons as l', 'l.id', '=', 'cla.lesson_id')
            ->join('modules as m', 'm.id', '=', 'l.module_id')
            ->where('cla.classroom_id', $student->classroom_id)
            ->where('cla.status', 'active')
            ->where('l.is_active', 1)
            ->orderBy('m.grade_id')
            ->orderBy('m.order_index')
            ->orderBy('l.order_index')
            ->select([
                'l.id',
                'l.title',
                'l.description',
                'l.estimated_time',
                'cla.assigned_at',
                'cla.due_at',
                'm.grade_id',
                'm.title as module_title',
            ])
            ->get()
            ->toArray();
    }

    public function render()
    {
        return view('livewire.student.dashboard');
    }
}
