@extends('layouts.auth')

@section('title', 'Register — Woven')

@section('content')
    <h1>Create account</h1>
    <p class="subtitle">New accounts start with the student role</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">Name</label>
            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password">
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password">
        </div>

        <button type="submit" class="btn btn-primary">Create account</button>
    </form>

    <p class="auth-footer">
        Already registered?
        <a href="{{ route('login') }}">Sign in</a>
    </p>
@endsection
