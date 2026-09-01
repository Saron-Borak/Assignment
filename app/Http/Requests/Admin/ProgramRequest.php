<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    public function rules(): array
    {
        $id = $this->route('program')?->id;

        return [
            'faculty_id' => ['required', 'exists:faculties,id'],
            'code' => ['required', 'string', 'max:20', 'alpha_dash', Rule::unique('programs', 'code')->ignore($id)],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('code')) {
            $this->merge(['code' => strtoupper(trim($this->string('code')))]);
        }
    }
}
