<div class="vx-card">
    <div class="vx-card-header">
        <h3 class="vx-card-title">Tree Hierarchy</h3>
        <div style="display: flex; gap: 8px;">
            @if($useSave)
                <button type="button" class="vx-btn vx-btn-primary vx-btn-sm" onclick="window.location.reload()">Save Order</button>
            @endif
            @if($useRefresh)
                <button type="button" class="vx-btn vx-btn-subtle vx-btn-sm" onclick="window.location.reload()">Refresh</button>
            @endif
        </div>
    </div>

    <div class="vx-card-body">
        <div class="vx-tree-container">
            <ul class="vx-tree-root" style="list-style: none; padding-left: 0;">
                @foreach($items as $item)
                    <li class="vx-tree-item" style="padding: 8px 12px; margin-bottom: 6px; background: var(--vx-bg); border: 1px solid var(--vx-border); border-radius: var(--vx-radius);">
                        {!! $item['rendered_branch'] ?? '' !!}

                        @if(!empty($item['children']))
                            <ul class="vx-tree-children" style="list-style: none; padding-left: 24px; margin-top: 6px;">
                                @foreach($item['children'] as $child)
                                    <li class="vx-tree-item" style="padding: 8px 12px; margin-bottom: 6px; background: var(--vx-surface); border: 1px solid var(--vx-border); border-radius: var(--vx-radius);">
                                        {!! $child['rendered_branch'] ?? '' !!}
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
