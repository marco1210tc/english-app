<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;

class StudentAuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:50'],
            'pin'  => ['required','digits:4'],
        ]);

        $student = Student::where('code', $data['code'])->first();

        if (!$student || !$student->verifyPin($data['pin'])) {
            return back()
                ->withErrors(['pin' => 'Código o PIN incorrecto.'])
                ->onlyInput('code');
        }

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login', ['role' => 'student']);
    }
}
