@if(!empty($filters) || !empty($scopes))
    <div class="vx-card" style="margin-bottom: 16px;">
        <div class="vx-card-body" style="padding: 16px;">
            @if(!empty($scopes))
                <div class="vx-scope-tabs" style="display: flex; gap: 8px; margin-bottom: 12px; border-bottom: 1px solid var(--vx-border); padding-bottom: 8px;">
                    <a href="{{ request()->fullUrlWithQuery(['_scope_' => null]) }}" class="vx-btn vx-btn-xs {{ !request('_scope_') ? 'vx-btn-primary' : 'vx-btn-subtle' }}">
                        All
                    </a>
                    @foreach($scopes as $key => $scope)
                        <a href="{{ request()->fullUrlWithQuery(['_scope_' => $key]) }}" class="vx-btn vx-btn-xs {{ request('_scope_') === $key ? 'vx-btn-primary' : 'vx-btn-subtle' }}">
                            {{ $scope['label'] }}
                        </a>
                    @endforeach
                </div>
            @endif

            @if(!empty($filters))
                <form action="{{ request()->url() }}" method="GET" class="vx-filter-form" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: flex-end;">
                    @foreach(request()->except(array_keys($filters)) as $k => $v)
                        @if(is_string($v) || is_numeric($v))
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endforeach

                    @foreach($filters as $column => $filter)
                        <div style="min-width: 180px; flex: 1;">
                            <label class="vx-form-label" style="font-size: 0.75rem; text-transform: uppercase;">{{ $filter['label'] }}</label>
                            @if($filter['type'] === 'between')
                                <div style="display: flex; gap: 6px;">
                                    <input type="text" name="{{ $column }}[start]" value="{{ request("{$column}.start") }}" placeholder="From" class="vx-input vx-input-sm">
                                    <input type="text" name="{{ $column }}[end]" value="{{ request("{$column}.end") }}" placeholder="To" class="vx-input vx-input-sm">
                                </div>
                            @else
                                <input type="text" name="{{ $column }}" value="{{ request($column) }}" placeholder="{{ $filter['label'] }}" class="vx-input vx-input-sm">
                            @endif
                        </div>
                    @endforeach

                    <div style="display: flex; gap: 8px;">
                        <button type="submit" class="vx-btn vx-btn-primary vx-btn-sm">Filter</button>
                        <a href="{{ request()->url() }}" class="vx-btn vx-btn-subtle vx-btn-sm">Reset</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
@endif
