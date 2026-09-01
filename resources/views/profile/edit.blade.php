@extends('layouts.app')
@section('title', 'My profile')
@section('heading', 'My profile')
@section('subheading', $user->role->label().' account')

@section('content')
<div class="row g-3">
    <div class="col-lg-6">
        <x-page-card title="Profile details">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf @method('PATCH')
                <div class="card-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Full name</label>
                        <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" class="form-control" value="{{ $user->email }}" disabled readonly>
                        <div class="form-text">Contact the registry to change your email address.</div>
                    </div>
                    <div>
                        <label for="phone" class="form-label">Phone</label>
                        <input type="text" id="phone" name="phone" class="form-control @error('phone') is-invalid @enderror"
                               value="{{ old('phone', $user->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>Save profile</button>
                </div>
            </form>
        </x-page-card>
    </div>

    <div class="col-lg-6">
        <x-page-card title="Change password">
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current password</label>
                        <input type="password" id="current_password" name="current_password" autocomplete="current-password"
                               class="form-control @error('current_password') is-invalid @enderror" required>
                        @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">New password</label>
                        <input type="password" id="password" name="password" autocomplete="new-password" minlength="8"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="form-label">Confirm new password</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                               autocomplete="new-password" minlength="8" class="form-control" required>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    <button class="btn btn-primary"><i class="bi bi-key me-1"></i>Change password</button>
                </div>
            </form>
        </x-page-card>
    </div>
</div>
@endsection
