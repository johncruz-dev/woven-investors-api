@extends('layouts.education')

@section('title', 'New ticket')

@section('content')
    <p class="muted"><a href="{{ route('education.tickets.index') }}">← Tickets</a></p>
    <h1>New support ticket</h1>
    <p class="subtitle">Request help for a student</p>

    <div class="card" style="max-width:640px;">
        <form method="POST" action="{{ route('education.tickets.store') }}">
            @csrf

            <div class="field">
                <label for="student_id">Student</label>
                <select id="student_id" name="student_id" required>
                    @foreach ($students as $student)
                        <option value="{{ $student->id }}" @selected((string) old('student_id', request('student_id')) === (string) $student->id)>
                            {{ $student->name }} ({{ $student->external_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="field">
                <label for="subject">Subject</label>
                <input id="subject" type="text" name="subject" value="{{ old('subject') }}" required maxlength="255">
            </div>

            <div class="field">
                <label for="body">Details</label>
                <textarea id="body" name="body" rows="5" required>{{ old('body') }}</textarea>
            </div>

            <div class="field">
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->value }}" @selected(old('priority', 'normal') === $priority->value)>{{ $priority->label() }}</option>
                    @endforeach
                </select>
            </div>

            @if ($user->isEducationStaff())
                <div class="field">
                    <label for="assigned_to">Assign to (optional)</label>
                    <select id="assigned_to" name="assigned_to">
                        <option value="">Unassigned</option>
                        @foreach ($staff as $member)
                            <option value="{{ $member->id }}" @selected((string) old('assigned_to') === (string) $member->id)>{{ $member->name }} ({{ $member->role->label() }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <button class="btn btn-primary" type="submit">Create ticket</button>
        </form>
    </div>
@endsection
