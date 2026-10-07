@extends('layouts.education')

@section('title', 'Platform settings')

@section('content')
    <p class="muted"><a href="{{ route('education.admin.users.index') }}">← Admin</a></p>
    <h1>Platform settings</h1>
    <div class="card" style="max-width:640px;">
        <form method="POST" action="{{ route('education.admin.settings.update') }}">
            @csrf
            @method('PUT')
            <div class="field"><label>Platform name</label><input type="text" name="platform_name" value="{{ $settings['platform_name'] }}" required></div>
            <div class="field"><label>Support email</label><input type="email" name="support_email" value="{{ $settings['support_email'] }}" required></div>
            <div class="field">
                <label>Allow registration</label>
                <select name="allow_registration">
                    <option value="1" @selected($settings['allow_registration'] === '1')>Yes</option>
                    <option value="0" @selected($settings['allow_registration'] === '0')>No</option>
                </select>
            </div>
            <div class="field">
                <label>Maintenance mode</label>
                <select name="maintenance_mode">
                    <option value="0" @selected($settings['maintenance_mode'] === '0')>Off</option>
                    <option value="1" @selected($settings['maintenance_mode'] === '1')>On</option>
                </select>
            </div>
            <button class="btn btn-primary" type="submit">Save settings</button>
        </form>
    </div>
@endsection
