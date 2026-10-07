@extends('layouts.education')

@section('title', 'Edit · '.$lesson->title)

@section('content')
    <p class="muted"><a href="{{ route('education.lessons.show', [$course, $lesson]) }}">← {{ $lesson->title }}</a></p>
    <h1>Edit lesson</h1>
    <p class="subtitle">{{ $course->title }} · Position {{ $lesson->position }}</p>

    <div class="card" style="max-width:720px;">
        <form method="POST" action="{{ route('education.lessons.update', [$course, $lesson]) }}">
            @csrf
            @method('PUT')
            <div class="field">
                <label for="title">Title</label>
                <input id="title" type="text" name="title" value="{{ old('title', $lesson->title) }}" required>
            </div>
            <div class="field">
                <label for="content">Content</label>
                <textarea id="content" name="content" rows="10">{{ old('content', $lesson->content) }}</textarea>
            </div>
            <label class="remember">
                <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $lesson->is_published))>
                Published (visible to enrolled students)
            </label>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save lesson</button>
                <a class="btn btn-secondary" href="{{ route('education.lessons.show', [$course, $lesson]) }}">Cancel</a>
            </div>
        </form>
    </div>
@endsection
