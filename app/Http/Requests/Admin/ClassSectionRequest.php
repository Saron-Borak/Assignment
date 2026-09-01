<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClassSectionRequest extends FormRequest
{
    public function rules(): array
    {
        $section = $this->route('class_section');

        return [
            'course_id' => ['required', 'exists:courses,id'],
            'semester_id' => ['required', 'exists:semesters,id'],
            'lecturer_id' => ['required', 'exists:lecturers,id'],
            'section_code' => [
                'required', 'string', 'max:10', 'alpha_num',
                // The same course cannot run two sections with one letter in a
                // single semester.
                Rule::unique('class_sections', 'section_code')
                    ->where('course_id', $this->integer('course_id'))
                    ->where('semester_id', $this->integer('semester_id'))
                    ->ignore($section?->id),
            ],
            'room' => ['nullable', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:500'],

            'schedules' => ['array'],
            'schedules.*.day_of_week' => ['required_with:schedules.*.start_time', 'nullable', 'integer', 'between:1,7'],
            'schedules.*.start_time' => ['required_with:schedules.*.day_of_week', 'nullable', 'date_format:H:i'],
            'schedules.*.end_time' => ['required_with:schedules.*.start_time', 'nullable', 'date_format:H:i', 'after:schedules.*.start_time'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->filled('section_code')) {
            $this->merge(['section_code' => strtoupper(trim($this->string('section_code')))]);
        }
    }

    /**
     * Timetable rows the admin left completely blank are dropped rather than
     * failing validation.
     *
     * @return array<int, array{day_of_week:int, start_time:string, end_time:string}>
     */
    public function schedules(): array
    {
        return collect($this->input('schedules', []))
            ->filter(fn ($row) => filled($row['day_of_week'] ?? null) && filled($row['start_time'] ?? null))
            ->map(fn ($row) => [
                'day_of_week' => (int) $row['day_of_week'],
                'start_time' => $row['start_time'].':00',
                'end_time' => $row['end_time'].':00',
                'room' => $row['room'] ?? null,
            ])
            ->values()
            ->all();
    }
}
