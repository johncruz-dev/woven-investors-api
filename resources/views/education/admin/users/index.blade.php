@extends('layouts.education')

@section('title', 'Admin · Users')

@section('content')
    <div class="toolbar">
        <div>
            <h1>User management & permissions</h1>
            <p class="subtitle">Administrators manage users and roles</p>
        </div>
        <div style="display:flex;gap:0.5rem;flex-wrap:wrap;">
            <a class="btn btn-secondary" href="{{ route('education.admin.settings.edit') }}">Settings</a>
            <a class="btn btn-secondary" href="{{ route('education.admin.reports.index') }}">Reports</a>
            <a class="btn btn-secondary" href="{{ route('education.admin.subscriptions.index') }}">Subscriptions</a>
            <a class="btn btn-secondary" href="{{ route('education.admin.security.index') }}">Security</a>
        </div>
    </div>

    <div class="card">
        <h2 style="font-size:1rem;margin-bottom:1rem;">Create user</h2>
        <form method="POST" action="{{ route('education.admin.users.store') }}">
            @csrf
            <div class="field"><label>Name</label><input type="text" name="name" required></div>
            <div class="field"><label>Email</label><input type="email" name="email" required></div>
            <div class="field"><label>Password</label><input type="password" name="password" required></div>
            <div class="field">
                <label>Role</label>
                <select name="role" required>
                    @foreach ($roles as $role)
                        <option value="{{ $role->value }}">{{ $role->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Create user</button>
        </form>
    </div>

    <div class="card">
        <table>
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Active</th><th>Update</th></tr></thead>
            <tbody>
                @foreach ($users as $managed)
                    <tr>
                        <td>{{ $managed->name }}</td>
                        <td>{{ $managed->email }}</td>
                        <td colspan="3">
                            <form method="POST" action="{{ route('education.admin.users.update', $managed) }}" style="display:flex;gap:0.5rem;align-items:center;flex-wrap:wrap;">
                                @csrf
                                @method('PUT')
                                <select name="role">
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->value }}" @selected($managed->role === $role)>{{ $role->label() }}</option>
                                    @endforeach
                                </select>
                                <select name="is_active">
                                    <option value="1" @selected($managed->is_active)>Active</option>
                                    <option value="0" @selected(! $managed->is_active)>Inactive</option>
                                </select>
                                <button class="btn btn-secondary" type="submit">Save</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="pagination">{{ $users->links('pagination::simple-default') }}</div>
    </div>
@endsection
