<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Education') — Woven</title>
    <link rel="stylesheet" href="{{ asset('css/woven.css') }}">
</head>
<body class="app-body">
    <div class="sidebar-overlay" data-sidebar-overlay></div>

    <aside class="sidebar" id="app-sidebar" aria-label="Sidebar">
        <div class="sidebar-header">
            <a class="brand" href="{{ route('education.dashboard') }}">
                <span class="brand-text">Woven <span>Education</span></span>
            </a>
            <button type="button" class="sidebar-toggle sidebar-collapse" data-sidebar-collapse aria-label="Collapse sidebar" title="Collapse sidebar" aria-expanded="true">
                <x-icon name="collapse" class="icon icon-collapse" />
            </button>
            <button type="button" class="sidebar-toggle sidebar-close" data-sidebar-close aria-label="Close menu">
                <x-icon name="close" class="icon" />
            </button>
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('education.dashboard') }}" class="{{ request()->routeIs('education.dashboard') ? 'active' : '' }}">
                <x-icon name="home" class="nav-icon" /><span class="nav-label">Dashboard</span>
            </a>
            <a href="{{ route('education.courses.index') }}" class="{{ request()->routeIs('education.courses.*') || request()->routeIs('education.lessons.*') || request()->routeIs('education.assignments.*') ? 'active' : '' }}">
                <x-icon name="courses" class="nav-icon" /><span class="nav-label">Courses</span>
            </a>
            @if ($user->canMessageTeachers())
                <a href="{{ route('education.messages.index') }}" class="{{ request()->routeIs('education.messages.*') ? 'active' : '' }}">
                    <x-icon name="messages" class="nav-icon" /><span class="nav-label">Messages</span>
                </a>
            @endif
            @if ($user->isEducationStaff())
                <a href="{{ route('education.students.index') }}" class="{{ request()->routeIs('education.students.*') ? 'active' : '' }}">
                    <x-icon name="students" class="nav-icon" /><span class="nav-label">Students</span>
                </a>
            @endif
            <a href="{{ route('education.tickets.index') }}" class="{{ request()->routeIs('education.tickets.*') ? 'active' : '' }}">
                <x-icon name="support" class="nav-icon" /><span class="nav-label">Support</span>
            </a>
            @if ($user->canImportRoster())
                <a href="{{ route('education.roster.create') }}" class="{{ request()->routeIs('education.roster.*') ? 'active' : '' }}">
                    <x-icon name="roster" class="nav-icon" /><span class="nav-label">Roster</span>
                </a>
            @endif
            @if ($user->canManageUsersAndPermissions())
                <a href="{{ route('education.admin.users.index') }}" class="{{ request()->routeIs('education.admin.*') ? 'active' : '' }}">
                    <x-icon name="admin" class="nav-icon" /><span class="nav-label">Admin</span>
                </a>
            @endif
            <a href="{{ route('education.profile.edit') }}" class="{{ request()->routeIs('education.profile.*') ? 'active' : '' }}">
                <x-icon name="profile" class="nav-icon" /><span class="nav-label">Profile</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-logout" type="submit" title="Sign out">
                    <span class="btn-logout-text">Sign out</span>
                    <span class="btn-logout-icon"><x-icon name="logout" class="icon" /></span>
                </button>
            </form>
        </div>
    </aside>

    <div class="app-main">
        <div class="mobile-bar">
            <button type="button" class="mobile-menu-btn" data-sidebar-open aria-label="Open menu">
                <x-icon name="menu" class="icon" />
            </button>
            <span class="mobile-title">@yield('title', 'Education')</span>
        </div>

        <main class="container">
            @if (session('status'))
                <div class="flash">{{ session('status') }}</div>
            @endif

            @if ($errors->any())
                <div class="error">{{ $errors->first() }}</div>
            @endif

            @yield('content')
        </main>
    </div>

    @include('partials.sidebar-script')
</body>
</html>
