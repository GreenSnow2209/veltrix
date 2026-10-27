<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ config('veltrix.theme', 'system') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ Veltrix::title() }}</title>

    @if(Veltrix::favicon())
        <link rel="shortcut icon" href="{{ Veltrix::favicon() }}">
    @endif

    <!-- Built-in Veltrix Modern Styling -->
    @foreach(Veltrix::css() as $css)
        <link rel="stylesheet" href="{{ $css }}">
    @endforeach

    @foreach(Veltrix::style() as $style)
        <style>{!! $style !!}</style>
    @endforeach

    <script>
        // Inline theme setup before body rendering to prevent theme flicker
        (function() {
            var theme = localStorage.getItem('vx-theme') || '{{ config('veltrix.theme', 'system') }}';
            if (theme === 'system') {
                theme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
</head>
<body>
    <div class="vx-app-layout">
        @include('veltrix::layouts.partials.sidebar')

        <div class="vx-main-wrap">
            @include('veltrix::layouts.partials.header')

            <main class="vx-main-content">
                @if(session()->has('success'))
                    <div class="vx-toast vx-toast-success" style="margin-bottom: 16px;">
                        {{ session('success')->first('title') ?: session('success')->first('message') }}
                    </div>
                @endif

                @if(session()->has('error'))
                    <div class="vx-toast vx-toast-error" style="margin-bottom: 16px;">
                        {{ session('error')->first('title') ?: session('error')->first('message') }}
                    </div>
                @endif

                @if(session()->has('toastr'))
                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            Veltrix.toast("{{ session('toastr')->first('message') }}", "{{ session('toastr')->first('type') ?: 'success' }}");
                        });
                    </script>
                @endif

                @yield('content')
            </main>

            <footer class="vx-footer">
                <div>
                    <strong>{{ config('veltrix.name', 'Veltrix') }}</strong> - {{ config('veltrix.tagline', 'Familiar to Build. Modern by Design.') }}
                </div>
                <div>
                    @if(config('veltrix.show_version', true))
                        <span>Version {{ \Veltrix\Veltrix::VERSION }}</span>
                    @endif
                    @if(config('veltrix.show_environment', true))
                        &middot; <span>Env: {{ app()->environment() }}</span>
                    @endif
                </div>
            </footer>
        </div>
    </div>

    <!-- Core Runtime Scripts -->
    @foreach(Veltrix::js() as $js)
        <script src="{{ $js }}"></script>
    @endforeach

    @foreach(Veltrix::script() as $script)
        <script>{!! $script !!}</script>
    @endforeach
</body>
</html>
