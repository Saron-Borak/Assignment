@extends('layouts.app')
@section('title', 'Edit student')
@section('heading', 'Edit '.$student->user->name)
@section('content')
<div class="row"><div class="col-lg-10 col-xl-9">
    <x-page-card>
        <form method="POST" action="{{ route('admin.students.update', $student) }}">
            @csrf @method('PUT')
            <div class="card-body">@include('admin.students._form')</div>
            <div class="card-footer bg-white d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save changes</button>
                <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </x-page-card>
</div></div>
@endsection
