@extends('layouts.app')
@section('title', 'Edit course')
@section('heading', 'Edit '.$course->code)
@section('content')
<div class="row"><div class="col-lg-9">
    <x-page-card>
        <form method="POST" action="{{ route('admin.courses.update', $course) }}">
            @csrf @method('PUT')
            <div class="card-body">@include('admin.courses._form')</div>
            <div class="card-footer bg-white d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save changes</button>
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </x-page-card>
</div></div>
@endsection
