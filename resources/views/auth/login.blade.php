@extends('layouts.auth')

@section('title', 'Sign in — Woven Investors')

@section('content')
    <h1>Sign in</h1>
    <p class="subtitle">Access the investors dashboard</p>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">
            @error('password')
                <p class="error">{{ $message }}</p>
            @enderror
        </div>

        <label class="remember">
            <input type="checkbox" name="remember">
            Remember me
        </label>

        <button type="submit" class="btn">Sign in</button>
    </form>

    @if (config('security.registration_enabled'))
        <p class="footer">
            No account?
            <a href="{{ route('register') }}">Create one</a>
        </p>
    @endif
@endsection
