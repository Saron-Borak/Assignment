@extends('layouts.app')
@section('title', 'Semesters')
@section('heading', 'Semesters')
@section('subheading', 'Academic periods that class sections belong to')

@section('toolbar')
    <a href="{{ route('admin.semesters.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg me-1"></i>New semester</a>
@endsection

@section('content')
<x-page-card>
    @if ($semesters->isEmpty())
        <x-empty-state icon="bi-calendar3" title="No semesters yet" message="Class sections must belong to a semester.">
            <a href="{{ route('admin.semesters.create') }}" class="btn btn-sm btn-primary">Create a semester</a>
        </x-empty-state>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead><tr><th>Code</th><th>Name</th><th>Period</th><th class="text-end">Sections</th><th>Status</th><th style="width:1%"></th></tr></thead>
                <tbody>
                @foreach ($semesters as $semester)
                    <tr>
                        <td><span class="badge text-bg-light border font-monospace">{{ $semester->code }}</span></td>
                        <td class="fw-semibold">{{ $semester->name }}</td>
                        <td class="small text-secondary text-nowrap">
                            {{ $semester->start_date->format('d M Y') }} &ndash; {{ $semester->end_date->format('d M Y') }}
                        </td>
                        <td class="text-end">{{ $semester->class_sections_count }}</td>
                        <td>
                            @if ($semester->is_active)
                                <span class="badge text-bg-success"><i class="bi bi-check-circle-fill me-1"></i>Active</span>
                            @else
                                <span class="badge text-bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-nowrap text-end">
                            <a href="{{ route('admin.semesters.edit', $semester) }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-pencil"></i></a>
                            <form method="POST" action="{{ route('admin.semesters.destroy', $semester) }}" class="d-inline"
                                  onsubmit="return confirm('Delete {{ $semester->code }}?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white">{{ $semesters->links() }}</div>
    @endif
</x-page-card>
@endsection
