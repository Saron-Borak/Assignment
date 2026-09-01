@extends('layouts.app')
@section('title', 'Edit class section')
@section('heading', 'Edit '.$section->label())
@section('content')
<div class="row"><div class="col-lg-10">
    <x-page-card>
        <form method="POST" action="{{ route('admin.class-sections.update', $section) }}">
            @csrf @method('PUT')
            <div class="card-body">@include('admin.class-sections._form')</div>
            <div class="card-footer bg-white d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save changes</button>
                <a href="{{ route('admin.class-sections.show', $section) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </x-page-card>
</div></div>
@endsection
