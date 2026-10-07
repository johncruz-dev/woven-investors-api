@extends('layouts.education')

@section('title', 'Manage students')

@section('content')
    <p class="muted"><a href="{{ route('education.courses.show', $course) }}">← {{ $course->title }}</a></p>
    <h1>Manage students</h1>
    <p class="subtitle">Enroll or remove students in this course</p>

    <div class="card">
        <form method="POST" action="{{ route('education.courses.students.add', $course) }}" class="toolbar">
            @csrf
            <div style="flex:1;">
                <select name="student_id" required>
                    <option value="">Select student</option>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Add student</button>
        </form>

        <table>
            <thead><tr><th>Name</th><th>Email</th><th></th></tr></thead>
            <tbody>
                @forelse ($course->enrollments as $enrollment)
                    <tr>
                        <td>{{ $enrollment->student->name }}</td>
                        <td>{{ $enrollment->student->email }}</td>
                        <td>
                            <form method="POST" action="{{ route('education.courses.students.remove', [$course, $enrollment->student]) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-secondary" type="submit">Remove</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="empty">No students enrolled.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
