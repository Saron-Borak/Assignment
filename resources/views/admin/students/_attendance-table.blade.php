<x-page-card title="Attendance by class" :subtitle="'Minimum required: '.config('attendance.min_percentage').'%'">
    @if ($stats->isEmpty())
        <x-empty-state icon="bi-list-check" title="Not enrolled in any class"
                       message="Attendance figures appear once the student is enrolled and classes have been held." />
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th>Class</th><th class="text-end">Held</th>
                        <th class="text-end">Present</th><th class="text-end">Late</th>
                        <th class="text-end">Absent</th><th class="text-end">Excused</th>
                        <th style="width:170px">Attendance</th>
                    </tr>
                </thead>
                <tbody>
                @foreach ($stats as $row)
                    <tr class="{{ $row->at_risk ? 'row-at-risk' : '' }}">
                        <td>
                            <div class="fw-semibold">{{ $row->course_code }}-{{ $row->section_code }}</div>
                            <div class="small text-secondary">{{ $row->course_title }}</div>
                        </td>
                        <td class="text-end">{{ $row->held }}</td>
                        <td class="text-end text-success">{{ $row->present }}</td>
                        <td class="text-end text-warning-emphasis">{{ $row->late }}</td>
                        <td class="text-end text-danger">{{ $row->absent }}</td>
                        <td class="text-end text-secondary">{{ $row->excused }}</td>
                        <td><x-percentage-bar :percentage="$row->percentage" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-page-card>
