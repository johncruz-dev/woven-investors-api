@extends('layouts.education')

@section('title', 'Students')

@section('content')
    <div class="toolbar">
        <div>
            <h1>Students</h1>
            <p class="subtitle">{{ $school->name }}</p>
        </div>
        @if ($user->canImportRoster())
            <a class="btn btn-primary" href="{{ route('education.roster.create') }}">Import CSV roster</a>
        @endif
    </div>

    <div class="card">
        <form method="GET" action="{{ route('education.students.index') }}" class="toolbar">
            <div style="flex:1;min-width:200px;">
                <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, or student ID">
            </div>
            <button class="btn btn-secondary" type="submit">Search</button>
        </form>

        @if ($students->count())
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Grade</th>
                        <th>Open tickets</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($students as $student)
                        <tr>
                            <td>{{ $student->external_id }}</td>
                            <td><a href="{{ route('education.students.show', $student) }}">{{ $student->name }}</a></td>
                            <td>{{ $student->email ?? '—' }}</td>
                            <td>{{ $student->grade_level ?? '—' }}</td>
                            <td>{{ $student->open_tickets_count }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="pagination">{{ $students->links('pagination::simple-default') }}</div>
        @else
            <p class="empty">No students found. Import a roster CSV to begin.</p>
        @endif
    </div>
@endsection
