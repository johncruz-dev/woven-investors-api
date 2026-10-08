@extends('layouts.education')

@section('title', 'Admin · Users')

@section('content')
    <div class="toolbar">
        <div>
            <h1>User management & permissions</h1>
            <p class="subtitle">Administrators manage users and roles</p>
        </div>
        <div class="inline-actions">
            <a class="btn btn-secondary" href="{{ route('education.admin.settings.edit') }}">Settings</a>
            <a class="btn btn-secondary" href="{{ route('education.admin.reports.index') }}">Reports</a>
            <a class="btn btn-secondary" href="{{ route('education.admin.subscriptions.index') }}">Subscriptions</a>
            <a class="btn btn-secondary" href="{{ route('education.admin.security.index') }}">Security</a>
            <button type="button" class="btn btn-primary" data-modal-open="create-user-modal">Create user</button>
        </div>
    </div>

    <div class="card">
        <div class="table-wrap">
            <table class="users-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $managed)
                        <tr>
                            <td>{{ $managed->name }}</td>
                            <td>{{ $managed->email }}</td>
                            <td colspan="3">
                                <form method="POST" action="{{ route('education.admin.users.update', $managed) }}" class="user-edit-row">
                                    @csrf
                                    @method('PUT')
                                    <select name="role" aria-label="Role for {{ $managed->name }}">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->value }}" @selected($managed->role === $role)>{{ $role->label() }}</option>
                                        @endforeach
                                    </select>
                                    <select name="is_active" aria-label="Status for {{ $managed->name }}">
                                        <option value="1" @selected($managed->is_active)>Active</option>
                                        <option value="0" @selected(! $managed->is_active)>Inactive</option>
                                    </select>
                                    <button class="btn btn-secondary" type="submit">Save</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="empty">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination', ['paginator' => $users])
    </div>

    @php
        $openCreateModal = $errors->hasAny(['name', 'email', 'password']) || old('name') !== null;
    @endphp
    <div class="modal {{ $openCreateModal ? 'is-open' : '' }}" id="create-user-modal" data-modal aria-hidden="{{ $openCreateModal ? 'false' : 'true' }}">
        <div class="modal-backdrop" data-modal-close></div>
        <div class="modal-dialog" role="dialog" aria-modal="true" aria-labelledby="create-user-title">
            <div class="modal-header">
                <h2 id="create-user-title">Create user</h2>
                <button type="button" class="modal-close" data-modal-close aria-label="Close">✕</button>
            </div>
            <form method="POST" action="{{ route('education.admin.users.store') }}" class="modal-body">
                @csrf
                <div class="field">
                    <label for="create-name">Name</label>
                    <input id="create-name" type="text" name="name" value="{{ old('name') }}" required autofocus>
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="create-email">Email</label>
                    <input id="create-email" type="email" name="email" value="{{ old('email') }}" required>
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="create-password">Password</label>
                    <input id="create-password" type="password" name="password" required>
                    @error('password')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="field">
                    <label for="create-role">Role</label>
                    <select id="create-role" name="role" required>
                        @foreach ($roles as $role)
                            <option value="{{ $role->value }}" @selected(old('role') === $role->value)>{{ $role->label() }}</option>
                        @endforeach
                    </select>
                    @error('role')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button class="btn btn-primary" type="submit">Create user</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
(() => {
    const modal = document.querySelector('[data-modal]');
    if (!modal) return;

    const open = () => {
        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');
        const focusEl = modal.querySelector('input, select, textarea, button');
        focusEl?.focus();
    };

    const close = () => {
        modal.classList.remove('is-open');
        modal.setAttribute('aria-hidden', 'true');
    };

    document.querySelectorAll('[data-modal-open="create-user-modal"]').forEach((btn) => {
        btn.addEventListener('click', open);
    });

    modal.querySelectorAll('[data-modal-close]').forEach((el) => {
        el.addEventListener('click', close);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            close();
        }
    });
})();
</script>
@endpush
