<header class="vx-header">
    <div class="vx-header-left">
        <button type="button" class="vx-btn vx-btn-subtle vx-btn-sm" onclick="document.querySelector('.vx-sidebar').classList.toggle('collapsed')">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
        </button>
        <span class="vx-text-muted vx-text-sm">{{ config('veltrix.tagline', 'Familiar to Build. Modern by Design.') }}</span>
    </div>

    <div class="vx-header-right">
        <!-- Theme Toggle -->
        <button type="button" class="vx-btn vx-btn-subtle vx-btn-sm" onclick="Veltrix.toggleTheme()" title="Toggle Dark/Light Mode">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
        </button>

        @if(Veltrix::user())
            <div class="vx-user-dropdown" style="display: flex; align-items: center; gap: 10px;">
                <img src="{{ Veltrix::user()->avatar }}" alt="{{ Veltrix::user()->name }}" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
                <span style="font-size: 0.875rem; font-weight: 500;">{{ Veltrix::user()->name }}</span>
                <a href="{{ veltrix_base_path('auth/setting') }}" class="vx-btn vx-btn-subtle vx-btn-xs">Profile</a>
                <a href="{{ veltrix_base_path('auth/logout') }}" class="vx-btn vx-btn-subtle vx-btn-xs vx-text-danger">Logout</a>
            </div>
        @endif
    </div>
</header>
