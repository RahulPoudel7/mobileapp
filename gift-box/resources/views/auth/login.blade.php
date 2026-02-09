<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <style>
        * { box-sizing: border-box; font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4f46e5, #ec4899);
        }

        .card {
            background: #ffffff;
            padding: 2.5rem 2.75rem;
            border-radius: 1rem;
            box-shadow: 0 20px 35px rgba(15, 23, 42, 0.25);
            width: 100%;
            max-width: 380px;
        }

        .title {
            margin: 0 0 0.25rem;
            font-size: 1.6rem;
            font-weight: 700;
            color: #111827;
            text-align: center;
        }

        .subtitle {
            margin: 0 0 1.75rem;
            font-size: 0.9rem;
            color: #6b7280;
            text-align: center;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.35rem;
        }

        input {
            width: 100%;
            padding: 0.65rem 0.8rem;
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            font-size: 0.95rem;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s, transform 0.05s;
        }

        input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }

        .field {
            margin-bottom: 1rem;
        }

        .btn {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border-radius: 0.5rem;
            border: none;
            background: linear-gradient(135deg, #4f46e5, #6366f1);
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            margin-top: 0.4rem;
            transition: transform 0.05s, box-shadow 0.15s, filter 0.15s;
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.35);
        }

        .btn:hover {
            filter: brightness(1.05);
        }

        .btn:active {
            transform: translateY(1px);
            box-shadow: 0 6px 14px rgba(79, 70, 229, 0.4);
        }

        .error {
            background: #fef2f2;
            color: #b91c1c;
            border-radius: 0.5rem;
            padding: 0.6rem 0.75rem;
            font-size: 0.85rem;
            margin-bottom: 1rem;
            border: 1px solid #fecaca;
        }

        .meta {
            margin-top: 0.75rem;
            font-size: 0.78rem;
            color: #9ca3af;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1 class="title">Gift Box Admin</h1>
        <p class="subtitle">Sign in with your admin account to manage gifts and users.</p>

        @if ($errors->any())
            <div class="error">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="field">
                <label for="email">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                >
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                >
            </div>

            <button type="submit" class="btn">
                Log in
            </button>

            <p class="meta">Admin access only · {{ date('Y') }}</p>
        </form>
    </div>
</body>
</html>
