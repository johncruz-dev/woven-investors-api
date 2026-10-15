@extends('layouts.education')

@section('title', 'My profile')

@section('content')
    <div class="toolbar">
        <div class="page-header">
            <h1>My profile</h1>
            <p class="subtitle">Manage how you appear to teachers, counselors, and classmates</p>
        </div>
    </div>

    <form method="POST" action="{{ route('education.profile.update') }}" enctype="multipart/form-data" class="profile-layout" id="profile-form">
        @csrf
        @method('PUT')
        <input type="hidden" name="remove_avatar" id="remove_avatar" value="0">

        <aside class="card profile-identity">
            <div class="profile-photo">
                <label class="profile-avatar-picker" for="avatar" title="Change profile photo">
                    <img
                        id="avatar-preview"
                        class="profile-avatar-img"
                        src="{{ $user->avatarUrl() }}"
                        alt="Profile photo"
                        data-default="{{ asset('images/default-avatar.svg') }}"
                    >
                    <span class="profile-avatar-overlay">
                        <x-icon name="camera" class="icon profile-camera-icon" />
                        <span>Change photo</span>
                    </span>
                </label>

                <label class="profile-camera-badge" for="avatar" title="Upload photo" aria-label="Upload photo">
                    <x-icon name="camera" class="icon" />
                </label>

                <input
                    id="avatar"
                    class="profile-upload-input"
                    type="file"
                    name="avatar"
                    accept="image/jpeg,image/png,image/webp"
                >
            </div>

            <div class="profile-identity-copy">
                <h2>{{ $user->name }}</h2>
                <p class="muted">{{ $user->email }}</p>
                <span class="status status-graded">{{ $user->role->label() }}</span>
            </div>

            <div class="profile-avatar-actions">
                <p class="muted profile-upload-hint">Click the photo to upload. JPG, PNG, or WebP · max 2&nbsp;MB</p>
                <p class="profile-file-name muted" id="avatar-file-name" hidden></p>
                <button
                    type="button"
                    class="profile-remove-btn"
                    id="remove-avatar-btn"
                    @if (! $user->hasCustomAvatar()) hidden @endif
                >
                    Remove photo
                </button>
                @error('avatar')<p class="error">{{ $message }}</p>@enderror
            </div>
        </aside>

        <div class="card profile-details">
            <h2>Account details</h2>
            <p class="muted profile-section-intro">These details appear on your learning profile and messages.</p>

            <div class="profile-grid">
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="email">Email</label>
                    <input id="email" type="email" value="{{ $user->email }}" disabled>
                    <p class="muted field-hint">Email is managed by your school administrator.</p>
                </div>

                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="Optional">
                    @error('phone')<p class="error">{{ $message }}</p>@enderror
                </div>

                <div class="field">
                    <label for="role">Role</label>
                    <input id="role" type="text" value="{{ $user->role->label() }}" disabled>
                </div>

                <div class="field profile-grid-full">
                    <label for="bio">About you</label>
                    <textarea id="bio" name="bio" rows="5" placeholder="A short introduction for teachers and classmates">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')<p class="error">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="profile-actions">
                <button class="btn btn-primary" type="submit">Save changes</button>
            </div>
        </div>
    </form>

    <script>
        (() => {
            const input = document.getElementById('avatar');
            const preview = document.getElementById('avatar-preview');
            const fileName = document.getElementById('avatar-file-name');
            const removeFlag = document.getElementById('remove_avatar');
            const removeBtn = document.getElementById('remove-avatar-btn');
            if (!input || !preview) return;

            const defaultSrc = preview.dataset.default;

            input.addEventListener('change', () => {
                const file = input.files?.[0];
                if (!file) return;

                preview.src = URL.createObjectURL(file);
                removeFlag.value = '0';
                if (fileName) {
                    fileName.hidden = false;
                    fileName.textContent = file.name;
                }
                if (removeBtn) removeBtn.hidden = false;
            });

            removeBtn?.addEventListener('click', () => {
                input.value = '';
                preview.src = defaultSrc;
                removeFlag.value = '1';
                if (fileName) {
                    fileName.hidden = true;
                    fileName.textContent = '';
                }
                removeBtn.hidden = true;
            });
        })();
    </script>
@endsection
