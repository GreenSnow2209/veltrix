<div class="vx-card">
    <div class="vx-card-header">
        <h3 class="vx-card-title">Registered Extensions</h3>
    </div>
    <div class="vx-card-body">
        @if(empty($extensions))
            <p class="vx-text-muted vx-text-sm">No extensions registered. Extensions can be registered via <code>Veltrix::extend()</code>.</p>
        @else
            <ul style="list-style: none; padding-left: 0;">
                @foreach($extensions as $name => $class)
                    <li style="padding: 6px 0; border-bottom: 1px solid var(--vx-border);">
                        <strong>{{ $name }}</strong> &middot; <span class="vx-text-muted vx-text-sm">{{ $class }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
