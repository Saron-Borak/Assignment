@extends('layouts.app')
@section('title', 'New class section')
@section('heading', 'New class section')
@section('subheading', 'A course offering for one semester, taught by one lecturer')
@section('content')
<div class="row"><div class="col-lg-10">
    <x-page-card>
        <form method="POST" action="{{ route('admin.class-sections.store') }}">
            @csrf
            <div class="card-body">@include('admin.class-sections._form')</div>
            <div class="card-footer bg-white d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Create section</button>
                <a href="{{ route('admin.class-sections.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </x-page-card>
</div></div>
@endsection
