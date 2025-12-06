<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class SubmitActivityAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ya está pasando por auth+role:student, así que normalmente:
        return $this->user()?->isStudent() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Para quiz:
            // answers: { question_id: [option_ids...] }
            'answers' => ['nullable', 'array'],

            // Para drag_drop:
            // drag_drop_answers: { item_key: target }
            'drag_drop_answers' => ['nullable', 'array'],

            // Si envías started_at desde el frontend:
            'started_at' => ['nullable', 'date'],

            // attempt_id si lo creas en el show:
            'attempt_id' => ['nullable', 'integer', 'exists:student_activity_attempts,id'],
        ];
    }
}
