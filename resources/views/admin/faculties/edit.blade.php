@extends('layouts.app')
@section('title', 'Edit faculty')
@section('heading', 'Edit '.$faculty->code)

@section('content')
<div class="row"><div class="col-lg-8 col-xl-7">
    <x-page-card>
        <form method="POST" action="{{ route('admin.faculties.update', $faculty) }}">
            @csrf @method('PUT')
            <div class="card-body">@include('admin.faculties._form')</div>
            <div class="card-footer bg-white d-flex gap-2">
                <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save changes</button>
                <a href="{{ route('admin.faculties.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </x-page-card>
</div></div>
@endsection
