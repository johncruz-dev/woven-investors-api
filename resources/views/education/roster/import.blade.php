@extends('layouts.education')

@section('title', 'Import roster')

@section('content')
    <h1>Import student roster</h1>
    <p class="subtitle">
        CSV columns: <code>student_id,name,email,grade_level</code>
        @if ($school) · {{ $school->name }} @endif
    </p>

    <div class="card" style="max-width:560px;">
        <form method="POST" action="{{ route('education.roster.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="field">
                <label for="file">Roster CSV</label>
                <input id="file" type="file" name="file" accept=".csv,.txt" required>
            </div>
            <button class="btn btn-primary" type="submit">Upload & import</button>
        </form>
        <p class="muted" style="margin-top:1rem;">Existing student IDs in this school will be updated (upsert).</p>
    </div>
@endsection
