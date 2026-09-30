<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Woven Investors')</title>
    <style>
        :root {
            --bg: #f4f6f9;
            --card: #ffffff;
            --text: #1a2332;
            --muted: #64748b;
            --border: #e2e8f0;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --danger: #dc2626;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .auth-card {
            width: 100%;
            max-width: 420px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
        }

        .subtitle {
            color: var(--muted);
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        .field { margin-bottom: 1rem; }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 0.65rem 0.75rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 0.95rem;
        }

        .remember {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            margin-bottom: 1.25rem;
        }

        .btn {
            width: 100%;
            padding: 0.7rem 1rem;
            border: none;
            border-radius: 8px;
            background: var(--primary);
            color: #fff;
            font-weight: 600;
            cursor: pointer;
        }

        .btn:hover { background: var(--primary-hover); }

        .error {
            color: var(--danger);
            font-size: 0.8rem;
            margin-top: 0.35rem;
        }

        .footer {
            margin-top: 1.25rem;
            text-align: center;
            font-size: 0.875rem;
            color: var(--muted);
        }

        .footer a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        @yield('content')
    </div>
</body>
</html>
