<?php

namespace App\Http\Requests\Admin;

use App\Enums\StudentStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\Rules\Password;

class StudentRequest extends FormRequest
{
    public function rules(): array
    {
        $student = $this->route('student');
        $userId = $student?->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [$student ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'program_id' => ['required', 'exists:programs,id'],
            'student_no' => ['required', 'string', 'max:30', Rule::unique('students', 'student_no')->ignore($student?->id)],
            'intake_year' => ['required', 'integer', 'min:2000', 'max:'.(date('Y') + 1)],
            'status' => ['required', new Enum(StudentStatus::class)],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
