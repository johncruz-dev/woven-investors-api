@extends('layouts.education')

@section('title', 'My profile')

@section('content')
    <h1>My profile</h1>
    <p class="subtitle">Create and manage your account profile</p>

    <div class="card" style="max-width:640px;">
        <form method="POST" action="{{ route('education.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="name">Name</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" value="{{ $user->email }}" disabled>
            </div>
            <div class="field">
                <label for="phone">Phone</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}">
            </div>
            <div class="field">
                <label for="bio">Bio</label>
                <textarea id="bio" name="bio" rows="4">{{ old('bio', $user->bio) }}</textarea>
            </div>
            <p class="muted">Role: {{ $user->role->label() }}</p>
            <button class="btn btn-primary" type="submit" style="margin-top:1rem;">Save profile</button>
        </form>
    </div>
@endsection
