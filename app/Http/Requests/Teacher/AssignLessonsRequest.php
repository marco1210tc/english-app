<?php

namespace App\Http\Requests\Teacher;

use Illuminate\Foundation\Http\FormRequest;

class AssignLessonsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy en controller
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    // public function rules(): array
    // {
    //     return [
    //         'lesson_ids'   => ['required','array','min:1'],
    //         'lesson_ids.*' => ['integer','exists:lessons,id'],
    //         'due_at'       => ['nullable','date'],
    //     ];
    // }

    public function rules(): array
    {
        return [
            'lessons'              => ['required', 'array', 'min:1'],
            'lessons.*.id'         => ['required', 'integer', 'exists:lessons,id'],
            'lessons.*.due_at'     => ['nullable', 'date'],
        ];
    }
}
