<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ config('veltrix.theme', 'system') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login &middot; {{ Veltrix::title() }}</title>

    @foreach(Veltrix::css() as $css)
        <link rel="stylesheet" href="{{ $css }}">
    @endforeach

    <style>
        .vx-login-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--vx-bg);
            padding: 20px;
        }
        .vx-login-box {
            width: 100%;
            max-width: 420px;
            background: var(--vx-card-bg);
            border: 1px solid var(--vx-border);
            border-radius: 12px;
            box-shadow: var(--vx-shadow-md);
            padding: 36px;
        }
        .vx-login-logo {
            text-align: center;
            margin-bottom: 24px;
        }
        .vx-login-logo .vx-brand {
            justify-content: center;
            font-size: 1.75rem;
            color: var(--vx-text);
        }
    </style>
</head>
<body class="vx-login-page">
    <div class="vx-login-box">
        <div class="vx-login-logo">
            <a href="{{ veltrix_base_path() }}" class="vx-brand">
                {!! config('veltrix.logo', '<b>Vel</b>trix') !!}
            </a>
            <p class="vx-text-muted vx-text-sm" style="margin-top: 6px;">{{ config('veltrix.tagline', 'Familiar to Build. Modern by Design.') }}</p>
        </div>

        <form action="{{ veltrix_base_path('auth/login') }}" method="POST">
            @csrf

            @if($errors->has('username'))
                <div class="vx-toast vx-toast-error" style="margin-bottom: 16px;">
                    {{ $errors->first('username') }}
                </div>
            @endif

            <div class="vx-form-group">
                <label class="vx-form-label" for="username">Username</label>
                <input type="text" name="username" id="username" class="vx-input" value="{{ old('username') }}" required autofocus placeholder="admin">
            </div>

            <div class="vx-form-group">
                <label class="vx-form-label" for="password">Password</label>
                <input type="password" name="password" id="password" class="vx-input" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
            </div>

            <div class="vx-form-group" style="display: flex; justify-content: space-between; align-items: center;">
                <label style="display: flex; align-items: center; gap: 8px; font-size: 0.875rem; cursor: pointer;">
                    <input type="checkbox" name="remember" value="1">
                    Remember me
                </label>
            </div>

            <button type="submit" class="vx-btn vx-btn-primary" style="width: 100%; padding: 10px; font-size: 1rem; margin-top: 8px;">
                Sign in to Dashboard
            </button>
        </form>
    </div>

    @foreach(Veltrix::js() as $js)
        <script src="{{ $js }}"></script>
    @endforeach
</body>
</html>
