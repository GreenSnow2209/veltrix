<aside class="vx-sidebar">
    <div class="vx-sidebar-header">
        <a href="{{ veltrix_base_path() }}" class="vx-brand">
            {!! config('veltrix.logo', '<b>Vel</b>trix') !!}
        </a>
    </div>

    <div class="vx-sidebar-nav">
        <ul class="vx-nav-list">
            @foreach(Veltrix::menu() as $item)
                @php
                    $hasChildren = !empty($item['children']);
                    $uri = $item['uri'] ?? '';
                    $href = $uri ? (url()->isValidUrl($uri) ? $uri : veltrix_url($uri)) : '#';
                    $isActive = $uri ? request()->is(trim(veltrix_base_path($uri), '/') . '*') : false;
                @endphp
                <li class="vx-nav-item">
                    <a href="{{ $href }}" class="vx-nav-link {{ $isActive ? 'active' : '' }}">
                        <span class="vx-nav-icon">
                            @if(($item['icon'] ?? '') === 'layout-dashboard')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"></rect><rect x="14" y="3" width="7" height="5"></rect><rect x="14" y="12" width="7" height="9"></rect><rect x="3" y="16" width="7" height="5"></rect></svg>
                            @elseif(($item['icon'] ?? '') === 'shield-check')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            @elseif(($item['icon'] ?? '') === 'users')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            @else
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle></svg>
                            @endif
                        </span>
                        <span>{{ $item['title'] }}</span>
                    </a>

                    @if($hasChildren)
                        <ul class="vx-nav-sublist">
                            @foreach($item['children'] as $child)
                                @php
                                    $childUri = $child['uri'] ?? '';
                                    $childHref = $childUri ? (url()->isValidUrl($childUri) ? $childUri : veltrix_url($childUri)) : '#';
                                    $childActive = $childUri ? request()->is(trim(veltrix_base_path($childUri), '/') . '*') : false;
                                @endphp
                                <li class="vx-nav-item">
                                    <a href="{{ $childHref }}" class="vx-nav-link {{ $childActive ? 'active' : '' }}">
                                        <span>{{ $child['title'] }}</span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </li>
            @endforeach
        </ul>
    </div>
</aside>
