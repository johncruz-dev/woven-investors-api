@extends('layouts.education')

@section('title', 'Create course')

@section('content')
    <p class="muted"><a href="{{ route('education.courses.index') }}">← Courses</a></p>
    <h1>Create course</h1>
    <div class="card" style="max-width:640px;">
        <form method="POST" action="{{ route('education.courses.store') }}">
            @csrf
            <div class="field">
                <label for="title">Title</label>
                <input id="title" type="text" name="title" value="{{ old('title') }}" required>
            </div>
            <div class="field">
                <label for="code">Code</label>
                <input id="code" type="text" name="code" value="{{ old('code') }}" placeholder="MATH-101">
            </div>
            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4">{{ old('description') }}</textarea>
            </div>
            <button class="btn btn-primary" type="submit">Create course</button>
        </form>
    </div>
@endsection
