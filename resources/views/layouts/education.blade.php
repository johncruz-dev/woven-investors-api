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
                <span class="brand-mark" aria-hidden="true">W</span>
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
            <p class="nav-section">Workspace</p>
            <a href="{{ route('education.dashboard') }}" class="{{ request()->routeIs('education.dashboard') ? 'active' : '' }}">
                <x-icon name="home" class="nav-icon" /><span class="nav-label">Dashboard</span>
            </a>
            <a href="{{ route('education.courses.index') }}" class="{{ request()->routeIs('education.courses.*') || request()->routeIs('education.lessons.*') || request()->routeIs('education.assignments.show') ? 'active' : '' }}">
                <x-icon name="courses" class="nav-icon" /><span class="nav-label">Courses</span>
            </a>
            @if ($user->canViewLessonsAndAssignments())
                <a href="{{ route('education.schedule.index') }}" class="{{ request()->routeIs('education.schedule.*') ? 'active' : '' }}">
                    <x-icon name="calendar" class="nav-icon" /><span class="nav-label">Schedule</span>
                </a>
            @endif
            @if ($user->canGradeAssignments())
                <a href="{{ route('education.assignments.queue') }}" class="{{ request()->routeIs('education.assignments.queue') ? 'active' : '' }}">
                    <x-icon name="grading" class="nav-icon" /><span class="nav-label">Grading</span>
                </a>
            @endif
            @if ($user->canMessageTeachers())
                <a href="{{ route('education.messages.index') }}" class="{{ request()->routeIs('education.messages.*') ? 'active' : '' }}">
                    <x-icon name="messages" class="nav-icon" />
                    <span class="nav-label">Messages</span>
                    @if (($unreadMessagesCount ?? 0) > 0)
                        <span class="nav-badge" title="{{ $unreadMessagesCount }} unread">
                            {{ $unreadMessagesCount > 99 ? '99+' : $unreadMessagesCount }}
                        </span>
                    @endif
                </a>
            @endif
            <a href="{{ route('education.notifications.index') }}" class="{{ request()->routeIs('education.notifications.*') ? 'active' : '' }}">
                <x-icon name="bell" class="nav-icon" />
                <span class="nav-label">Notifications</span>
                @if (($unreadNotificationsCount ?? 0) > 0)
                    <span class="nav-badge" title="{{ $unreadNotificationsCount }} unread">
                        {{ $unreadNotificationsCount > 99 ? '99+' : $unreadNotificationsCount }}
                    </span>
                @endif
            </a>

            @if ($user->isEducationStaff() || $user->canImportRoster() || $user->canManageUsersAndPermissions())
                <p class="nav-section">Manage</p>
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

            <p class="nav-section">Account</p>
            <a href="{{ route('education.profile.edit') }}" class="{{ request()->routeIs('education.profile.*') ? 'active' : '' }}">
                <x-icon name="profile" class="nav-icon" />
                <span class="nav-label">Profile</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <img class="sidebar-user-avatar" src="{{ $user->avatarUrl() }}" alt="">
                <div class="sidebar-user-meta">
                    <strong>{{ $user->name }}</strong>
                    <span>{{ $user->role->label() }}</span>
                </div>
            </div>
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
            @yield('content')
        </main>
    </div>

    @include('partials.sidebar-script')
    @include('partials.toastr')
    @stack('scripts')
</body>
</html>
