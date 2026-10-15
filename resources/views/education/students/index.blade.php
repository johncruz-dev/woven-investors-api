@extends('layouts.education')

@section('title', 'Students')

@section('content')
    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
            <p class="eyebrow">Directory</p>
            <h1>Students</h1>
            <p class="subtitle">{{ $school->name }} · directory for counselors and teachers</p>
        </div>
        <div class="inline-actions">
            <form method="GET" action="{{ route('education.students.index') }}" class="students-search">
                @if (request('per_page'))
                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                @endif
                <input type="text" name="q" value="{{ $search }}" placeholder="Search name, email, or ID" aria-label="Search students">
                <button class="btn btn-secondary" type="submit">Search</button>
            </form>
            @if ($user->canImportRoster())
                <a class="btn btn-primary" href="{{ route('education.roster.create') }}">Import CSV roster</a>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Grade</th>
                        <th>Open tickets</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>{{ $student->external_id }}</td>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->email ?? '—' }}</td>
                            <td>{{ $student->grade_level ?? '—' }}</td>
                            <td>
                                @if ($student->open_tickets_count > 0)
                                    <span class="status status-submitted">{{ $student->open_tickets_count }}</span>
                                @else
                                    <span class="muted">0</span>
                                @endif
                            </td>
                            <td class="table-actions">
                                <a class="btn btn-secondary" href="{{ route('education.students.show', $student) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty">No students found. Import a roster CSV to begin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $students])
    </div>
@endsection
