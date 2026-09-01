<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CourseRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('course')?->id;

        return [
            'faculty_id' => ['required', 'exists:faculties,id'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('courses', 'code')->ignore($id)],
            'title' => ['required', 'string', 'max:255'],
            'credit_hours' => ['required', 'integer', 'min:1', 'max:12'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim($this->string('code')))]);
        }
    }
}
