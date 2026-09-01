<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class LecturerRequest extends FormRequest
{
    public function rules(): array
    {
        $lecturer = $this->route('lecturer');
        $userId = $lecturer?->user_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($userId)],
            'phone' => ['nullable', 'string', 'max:30'],
            // Only required when creating; leaving it blank on edit keeps the
            // existing password.
            'password' => [$lecturer ? 'nullable' : 'required', 'confirmed', Password::min(8)],
            'faculty_id' => ['required', 'exists:faculties,id'],
            'staff_no' => ['required', 'string', 'max:30', Rule::unique('lecturers', 'staff_no')->ignore($lecturer?->id)],
            'title' => ['nullable', 'string', 'max:30'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
