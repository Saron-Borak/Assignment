@extends('layouts.app')
@section('title', 'Edit semester')
@section('heading', 'Edit '.$semester->code)
@section('content')
<div class="row"><div class="col-lg-8">
    <x-page-card>
        <form method="POST" action="{{ route('admin.semesters.update', $semester) }}">
            @csrf @method('PUT')
            <div class="card-body">@include('admin.semesters._form')</div>
            <div class="card-footer bg-white d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save changes</button>
                <a href="{{ route('admin.semesters.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </x-page-card>
</div></div>
@endsection
