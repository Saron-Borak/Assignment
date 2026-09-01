<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Enums\EnrollmentStatus;
use App\Enums\SessionStatus;
use App\Models\ClassSection;
use App\Models\Semester;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Read-side aggregation for attendance percentages.
 *
 * Percentages follow the university rule:
 *   attended = present (+ late, when policy counts it)
 *   total    = closed sessions, excluding those the student was excused from
 *   pct      = attended / total * 100
 *
 * Only closed sessions count, so an open or upcoming class never drags a
 * student's figure down. Every method aggregates in SQL - the reports must stay
 * flat regardless of how many students or sessions exist.
 */
class AttendanceReportService
{
    /**
     * The conditional SUM expressions shared by every aggregate below.
     *
     * @return array<string, string>
     */
    protected function selectExpressions(): array
    {
        $attended = config('attendance.count_late_as_present')
            ? "'".AttendanceStatus::Present->value."','".AttendanceStatus::Late->value."'"
            : "'".AttendanceStatus::Present->value."'";

        $present = AttendanceStatus::Present->value;
        $late = AttendanceStatus::Late->value;
        $absent = AttendanceStatus::Absent->value;
        $excused = AttendanceStatus::Excused->value;

        return [
            'held' => 'COUNT(s.id)',
            'recorded' => 'COUNT(r.id)',
            'attended' => "SUM(CASE WHEN r.status IN ({$attended}) THEN 1 ELSE 0 END)",
            'present' => "SUM(CASE WHEN r.status = '{$present}' THEN 1 ELSE 0 END)",
            'late' => "SUM(CASE WHEN r.status = '{$late}' THEN 1 ELSE 0 END)",
            'absent' => "SUM(CASE WHEN r.status = '{$absent}' THEN 1 ELSE 0 END)",
            'excused' => "SUM(CASE WHEN r.status = '{$excused}' THEN 1 ELSE 0 END)",
        ];
    }

    /**
     * @return array<int, \Illuminate\Database\Query\Expression<string>>
     */
    protected function selectRaw(array $extra = []): array
    {
        $columns = $extra;

        foreach ($this->selectExpressions() as $alias => $expression) {
            $columns[] = DB::raw("{$expression} as {$alias}");
        }

        return $columns;
    }

    /**
     * Turn raw counters into the derived figures the views render.
     */
    public function summarise(object $row): array
    {
        $held = (int) $row->held;
        $excused = (int) $row->excused;
        $attended = (int) $row->attended;

        // Excused absences leave the denominator rather than counting against
        // the student.
        $countable = max(0, $held - $excused);
        $percentage = $countable > 0 ? round($attended / $countable * 100, 1) : 0.0;

        return [
            'held' => $held,
            'countable' => $countable,
            'attended' => $attended,
            'present' => (int) $row->present,
            'late' => (int) $row->late,
            'absent' => (int) $row->absent,
            'excused' => $excused,
            'unmarked' => max(0, $held - (int) $row->recorded),
            'percentage' => $percentage,
            'at_risk' => $countable > 0 && $percentage < (float) config('attendance.min_percentage'),
        ];
    }

    /**
     * Enrollments joined to their closed sessions and whatever record exists for
     * that student in that session.
     *
     * Both joins are LEFT joins so a student on the roster still appears with
     * zeroes when their class has not met yet. Date filters live inside the join
     * condition rather than the WHERE clause - in a WHERE they would discard the
     * null rows that a LEFT JOIN exists to produce.
     */
    protected function baseQuery(?string $from = null, ?string $to = null): \Illuminate\Database\Query\Builder
    {
        return DB::table('enrollments as e')
            ->leftJoin('attendance_sessions as s', function ($join) use ($from, $to) {
                $join->on('s.class_section_id', '=', 'e.class_section_id')
                    ->where('s.status', '=', SessionStatus::Closed->value);

                if ($from) {
                    $join->whereDate('s.session_date', '>=', $from);
                }

                if ($to) {
                    $join->whereDate('s.session_date', '<=', $to);
                }
            })
            ->leftJoin('attendance_records as r', function ($join) {
                $join->on('r.attendance_session_id', '=', 's.id')
                    ->on('r.student_id', '=', 'e.student_id');
            })
            ->where('e.status', EnrollmentStatus::Enrolled->value);
    }

    /**
     * Every student on one section's roster, with their attendance figures.
     *
     * @return Collection<int, object>
     */
    public function classSectionStats(ClassSection $section, ?string $from = null, ?string $to = null): Collection
    {
        $rows = $this->baseQuery($from, $to)
            ->join('students as st', 'st.id', '=', 'e.student_id')
            ->join('users as u', 'u.id', '=', 'st.user_id')
            ->where('e.class_section_id', $section->id)
            ->groupBy('e.student_id', 'st.student_no', 'u.name', 'u.email')
            ->orderBy('u.name')
            ->select($this->selectRaw([
                'e.student_id',
                'st.student_no',
                'u.name',
                'u.email',
            ]))
            ->get();

        return $rows->map(fn (object $row) => (object) array_merge(
            (array) $row,
            $this->summarise($row),
        ));
    }

    /**
     * One student's figures across every section they are enrolled in.
     *
     * @return Collection<int, object>
     */
    public function studentOverall(Student $student, ?Semester $semester = null): Collection
    {
        $rows = $this->baseQuery()
            ->join('class_sections as cs', 'cs.id', '=', 'e.class_section_id')
            ->join('courses as c', 'c.id', '=', 'cs.course_id')
            ->join('semesters as sem', 'sem.id', '=', 'cs.semester_id')
            ->join('lecturers as l', 'l.id', '=', 'cs.lecturer_id')
            ->join('users as lu', 'lu.id', '=', 'l.user_id')
            ->where('e.student_id', $student->id)
            ->when($semester, fn ($q) => $q->where('cs.semester_id', $semester->id))
            ->groupBy(
                'e.class_section_id', 'cs.section_code', 'cs.room',
                'c.code', 'c.title', 'c.credit_hours',
                'sem.name', 'lu.name',
            )
            ->orderBy('c.code')
            ->select($this->selectRaw([
                'e.class_section_id',
                'cs.section_code',
                'cs.room',
                'c.code as course_code',
                'c.title as course_title',
                'c.credit_hours',
                'sem.name as semester_name',
                'lu.name as lecturer_name',
            ]))
            ->get();

        return $rows->map(fn (object $row) => (object) array_merge(
            (array) $row,
            $this->summarise($row),
        ));
    }

    /**
     * A single student's figures in a single section.
     */
    public function studentClassStats(Student $student, ClassSection $section): array
    {
        $row = $this->baseQuery()
            ->where('e.student_id', $student->id)
            ->where('e.class_section_id', $section->id)
            ->groupBy('e.student_id')
            ->select($this->selectRaw(['e.student_id']))
            ->first();

        return $row
            ? $this->summarise($row)
            : $this->summarise((object) [
                'held' => 0, 'recorded' => 0, 'attended' => 0,
                'present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0,
            ]);
    }

    /**
     * Section-level summary rows for the admin overview: one line per class,
     * showing how the cohort as a whole is attending.
     *
     * @return Collection<int, object>
     */
    public function sectionOverview(?Semester $semester = null, ?int $facultyId = null): Collection
    {
        $rows = $this->baseQuery()
            ->join('class_sections as cs', 'cs.id', '=', 'e.class_section_id')
            ->join('courses as c', 'c.id', '=', 'cs.course_id')
            ->join('lecturers as l', 'l.id', '=', 'cs.lecturer_id')
            ->join('users as lu', 'lu.id', '=', 'l.user_id')
            ->when($semester, fn ($q) => $q->where('cs.semester_id', $semester->id))
            ->when($facultyId, fn ($q) => $q->where('c.faculty_id', $facultyId))
            ->groupBy('e.class_section_id', 'cs.section_code', 'c.code', 'c.title', 'lu.name')
            ->orderBy('c.code')
            ->select($this->selectRaw([
                'e.class_section_id',
                'cs.section_code',
                'c.code as course_code',
                'c.title as course_title',
                'lu.name as lecturer_name',
                DB::raw('COUNT(DISTINCT e.student_id) as students'),
            ]))
            ->get();

        return $rows->map(fn (object $row) => (object) array_merge(
            (array) $row,
            $this->summarise($row),
        ));
    }

    /**
     * Students sitting below the university minimum, worst first.
     *
     * The percentage is derived per row rather than filtered in SQL because the
     * excused-absence adjustment happens in summarise(); filtering afterwards
     * keeps one definition of the rule.
     *
     * @return Collection<int, object>
     */
    public function lowAttendance(?Semester $semester = null, ?int $classSectionId = null): Collection
    {
        $rows = $this->baseQuery()
            ->join('students as st', 'st.id', '=', 'e.student_id')
            ->join('users as u', 'u.id', '=', 'st.user_id')
            ->join('programs as p', 'p.id', '=', 'st.program_id')
            ->join('class_sections as cs', 'cs.id', '=', 'e.class_section_id')
            ->join('courses as c', 'c.id', '=', 'cs.course_id')
            ->when($semester, fn ($q) => $q->where('cs.semester_id', $semester->id))
            ->when($classSectionId, fn ($q) => $q->where('e.class_section_id', $classSectionId))
            ->groupBy(
                'e.student_id', 'e.class_section_id', 'st.student_no',
                'u.name', 'u.email', 'p.name', 'cs.section_code', 'c.code', 'c.title',
            )
            ->select($this->selectRaw([
                'e.student_id',
                'e.class_section_id',
                'st.student_no',
                'u.name',
                'u.email',
                'p.name as program_name',
                'cs.section_code',
                'c.code as course_code',
                'c.title as course_title',
            ]))
            ->get();

        return $rows
            ->map(fn (object $row) => (object) array_merge((array) $row, $this->summarise($row)))
            ->filter(fn (object $row) => $row->at_risk)
            ->sortBy('percentage')
            ->values();
    }

    /**
     * Headline counters for the admin dashboard.
     */
    public function universityTotals(?Semester $semester = null): array
    {
        $row = $this->baseQuery()
            ->join('class_sections as cs', 'cs.id', '=', 'e.class_section_id')
            ->when($semester, fn ($q) => $q->where('cs.semester_id', $semester->id))
            ->select($this->selectRaw([]))
            ->first();

        return $this->summarise($row ?: (object) [
            'held' => 0, 'recorded' => 0, 'attended' => 0,
            'present' => 0, 'late' => 0, 'absent' => 0, 'excused' => 0,
        ]);
    }
}
