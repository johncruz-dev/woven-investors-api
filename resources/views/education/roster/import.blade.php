@extends('layouts.education')

@section('title', 'Import roster')

@section('content')
    <div class="toolbar">
        <div class="page-header" style="margin-bottom:0;">
            <p class="eyebrow">Students</p>
            <h1>Import student roster</h1>
            <p class="subtitle">
                Upload a CSV to create or update students
                @if ($school) for {{ $school->name }} @endif.
            </p>
        </div>
        <div class="inline-actions">
            <a class="btn btn-secondary" href="{{ asset('examples/roster-example.csv') }}" download>Download example CSV</a>
            <a class="btn btn-secondary" href="{{ route('education.students.index') }}">Back to students</a>
        </div>
    </div>

    <div class="roster-layout">
        <div class="card roster-form-card">
            <h2>Upload CSV</h2>
            <p class="muted profile-section-intro">Existing student IDs in this school will be updated automatically.</p>

            <form method="POST" action="{{ route('education.roster.store') }}" enctype="multipart/form-data" class="roster-form" id="roster-form">
                @csrf

                <label class="file-drop" for="file" id="file-drop">
                    <span class="file-drop-icon" aria-hidden="true">
                        <x-icon name="roster" class="icon" />
                    </span>
                    <span class="file-drop-title" id="file-drop-title">Choose a CSV file</span>
                    <span class="file-drop-hint muted">or drag and drop it here · .csv / .txt</span>
                    <input
                        id="file"
                        class="file-drop-input"
                        type="file"
                        name="file"
                        accept=".csv,.txt,text/csv"
                        required
                    >
                </label>
                @error('file')<p class="error">{{ $message }}</p>@enderror

                <div class="profile-actions">
                    <button class="btn btn-primary" type="submit">Upload &amp; import</button>
                </div>
            </form>
        </div>

        <aside class="card roster-help-card">
            <h2>Required columns</h2>
            <p class="muted profile-section-intro">Header row must match exactly:</p>
            <ul class="roster-columns">
                <li><code>student_id</code><span>Unique school student ID</span></li>
                <li><code>name</code><span>Full student name</span></li>
                <li><code>email</code><span>Optional contact email</span></li>
                <li><code>grade_level</code><span>Optional grade / year</span></li>
            </ul>
            <div class="roster-sample">
                <p class="muted">Example row</p>
                <code>S1001,Alex Student,alex@example.com,10</code>
            </div>
        </aside>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const input = document.getElementById('file');
    const drop = document.getElementById('file-drop');
    const title = document.getElementById('file-drop-title');
    if (!input || !drop || !title) return;

    const setFileName = () => {
        const file = input.files?.[0];
        title.textContent = file ? file.name : 'Choose a CSV file';
        drop.classList.toggle('has-file', Boolean(file));
    };

    input.addEventListener('change', setFileName);

    ['dragenter', 'dragover'].forEach((eventName) => {
        drop.addEventListener(eventName, (event) => {
            event.preventDefault();
            drop.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        drop.addEventListener(eventName, (event) => {
            event.preventDefault();
            drop.classList.remove('is-dragover');
        });
    });

    drop.addEventListener('drop', (event) => {
        const file = event.dataTransfer?.files?.[0];
        if (!file) return;
        input.files = event.dataTransfer.files;
        setFileName();
    });
})();
</script>
@endpush
