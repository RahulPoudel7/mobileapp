<!DOCTYPE html>
<html>
<head>
    <title>Gift Box Admin - @yield('title', 'Dashboard')</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        body {
            min-height: 100vh;
            background: radial-gradient(circle at top, #1f2937 0, #020617 55%, #020617 100%);
            color: #e5e7eb;
        }

        .shell {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 230px;
            background: #020617;
            padding: 1.5rem 1.25rem;
            border-right: 1px solid #111827;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .logo {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .nav-section-title {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #6b7280;
            margin-bottom: 0.75rem;
        }

        .nav-link {
            display: block;
            padding: 0.55rem 0.65rem;
            border-radius: 0.5rem;
            color: #d1d5db;
            font-size: 0.9rem;
            text-decoration: none;
            margin-bottom: 0.2rem;
            transition: background 0.12s, color 0.12s, transform 0.05s;
        }

        .nav-link:hover {
            background: #111827;
            color: #f9fafb;
            transform: translateX(1px);
        }

        .sidebar-footer {
            margin-top: 2rem;
            font-size: 0.75rem;
            color: #6b7280;
        }

        .main-shell {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 2rem;
            border-bottom: 1px solid #111827;
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(10px);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .tag {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            font-size: 0.75rem;
            margin-left: 0.75rem;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .user-pill {
            font-size: 0.85rem;
            padding: 0.25rem 0.6rem;
            border-radius: 999px;
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
        }

        .logout-btn {
            padding: 0.45rem 0.9rem;
            border-radius: 999px;
            border: 1px solid #4b5563;
            background: transparent;
            color: #e5e7eb;
            font-size: 0.85rem;
            cursor: pointer;
            transition: background 0.12s, color 0.12s;
        }

        .logout-btn:hover {
            background: #ef4444;
            border-color: #f97373;
            color: #f9fafb;
        }

        .main {
            flex: 1;
            padding: 2rem 2.25rem 3rem;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.25rem;
        }

        .card {
            background: #020617;
            border-radius: 1rem;
            padding: 1.4rem 1.5rem;
            border: 1px solid #1f2937;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.75);
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.35rem;
        }

        .card-text {
            font-size: 0.88rem;
            color: #9ca3af;
            margin-bottom: 0.9rem;
        }

        .card-link {
            display: inline-flex;
            align-items: center;
            font-size: 0.87rem;
            font-weight: 500;
            color: #a5b4fc;
            text-decoration: none;
        }

        .card-link:hover {
            text-decoration: underline;
        }

        .badge {
            display: inline-block;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            background: rgba(129, 140, 248, 0.2);
            color: #e0e7ff;
            font-size: 0.7rem;
            margin-left: 0.4rem;
        }

        .flash, .error {
            margin-bottom: 1rem;
            padding: 0.6rem 0.75rem;
            border-radius: 0.6rem;
            font-size: 0.85rem;
        }

        .flash {
            background: rgba(34, 197, 94, 0.12);
            border: 1px solid rgba(34, 197, 94, 0.4);
            color: #bbf7d0;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="shell">
        <aside class="sidebar">
            <div>
                <div class="logo">Gift Box Admin</div>

                <div>
                    <div class="nav-section-title">Navigation</div>
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                    <a href="{{ route('admin.gifts.index') }}" class="nav-link">Gifts</a>
                    <a href="{{ route('admin.users.index') }}" class="nav-link">Users</a>
                    <a href="{{ route('admin.orders.index') }}" class="nav-link">Orders</a>
                    <a href="{{ route('admin.categories.index') }}" class="nav-link">Categories</a>
                </div>
            </div>

            <div class="sidebar-footer">
                {{ date('Y') }} · Admin panel
            </div>
        </aside>

        <div class="main-shell">
            <header class="topbar">
                <div class="page-title">
                    @yield('title', 'Dashboard')
                    <span class="tag">Control center</span>
                </div>

                <div class="top-actions">
                    <div class="user-pill">
                        Admin logged in
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </header>

            <main class="main">
                @if (session('success'))
                    <div class="flash">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="error">{{ session('error') }}</div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
