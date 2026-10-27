<div class="vx-grid-wrapper">
    @if($options['filter'] && !empty($filterHtml))
        {!! $filterHtml !!}
    @endif

    <div class="vx-card">
        <!-- Grid Toolbar -->
        <div class="vx-card-header" style="flex-wrap: wrap; gap: 12px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                @if($options['create_button'])
                    <a href="{{ rtrim($resourcePath, '/') }}/create" class="vx-btn vx-btn-primary vx-btn-sm">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                        <span>New</span>
                    </a>
                @endif

                @if($options['row_selector'])
                    <button type="button" class="vx-btn vx-btn-danger vx-btn-sm" onclick="Veltrix.handleBatchDelete('{{ $resourcePath }}')">
                        Batch Delete
                    </button>
                @endif
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <!-- Quick Search Input -->
                <form action="{{ request()->url() }}" method="GET" style="display: flex;">
                    @foreach(request()->except(['_search_', 'page']) as $k => $v)
                        @if(is_string($v) || is_numeric($v))
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endforeach
                    <input type="text" name="_search_" value="{{ request('_search_') }}" placeholder="Quick search..." class="vx-input vx-input-sm" style="width: 200px; border-top-right-radius: 0; border-bottom-right-radius: 0;">
                    <button type="submit" class="vx-btn vx-btn-subtle vx-btn-sm" style="border-top-left-radius: 0; border-bottom-left-radius: 0; border-left: none;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </button>
                </form>

                @if($options['exporter'])
                    <a href="{{ request()->fullUrlWithQuery(['_export_' => 1]) }}" class="vx-btn vx-btn-subtle vx-btn-sm" title="Export CSV">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                        <span>Export</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- Table View -->
        <div class="vx-table-responsive" style="border: none; border-radius: 0;">
            <table class="vx-table">
                <thead>
                    <tr>
                        @if($options['row_selector'])
                            <th style="width: 38px; text-align: center;">
                                <input type="checkbox" class="vx-select-all">
                            </th>
                        @endif

                        @foreach($columns as $column)
                            <th>
                                @if($column->isSortable())
                                    @php
                                        $currentSort = request('_sort');
                                        $isSorted = is_array($currentSort) && ($currentSort['column'] ?? '') === $column->getName();
                                        $nextType = ($isSorted && ($currentSort['type'] ?? '') === 'asc') ? 'desc' : 'asc';
                                        $sortUrl = request()->fullUrlWithQuery(['_sort' => ['column' => $column->getName(), 'type' => $nextType]]);
                                    @endphp
                                    <a href="{{ $sortUrl }}" class="vx-link" style="color: inherit; display: inline-flex; align-items: center; gap: 4px;">
                                        <span>{{ $column->getLabel() }}</span>
                                        @if($isSorted)
                                            <span style="font-size: 0.75rem;">{{ $nextType === 'asc' ? '▼' : '▲' }}</span>
                                        @else
                                            <span style="opacity: 0.3; font-size: 0.75rem;">⇅</span>
                                        @endif
                                    </a>
                                @else
                                    {{ $column->getLabel() }}
                                @endif
                            </th>
                        @endforeach

                        @if($options['actions'])
                            <th style="text-align: right; width: 140px;">Actions</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            @if($options['row_selector'])
                                <td style="text-align: center;">
                                    <input type="checkbox" value="{{ $row['id'] }}" class="vx-row-select">
                                </td>
                            @endif

                            @foreach($columns as $column)
                                <td>
                                    {!! $row['columns'][$column->getName()] !!}
                                </td>
                            @endforeach

                            @if($options['actions'])
                                <td style="text-align: right; white-space: nowrap;">
                                    <a href="{{ rtrim($resourcePath, '/') }}/{{ $row['id'] }}" class="vx-btn vx-btn-xs vx-btn-subtle" title="Show">
                                        View
                                    </a>
                                    <a href="{{ rtrim($resourcePath, '/') }}/{{ $row['id'] }}/edit" class="vx-btn vx-btn-xs vx-btn-subtle" title="Edit">
                                        Edit
                                    </a>
                                    <button type="button" class="vx-btn vx-btn-xs vx-btn-subtle vx-text-danger" onclick="Veltrix.handleDelete('{{ rtrim($resourcePath, '/') }}/{{ $row['id'] }}')">
                                        Delete
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($columns) + 2 }}" style="text-align: center; padding: 40px; color: var(--vx-text-muted);">
                                No records found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Footer -->
        @if($options['pagination'] && $paginator)
            <div class="vx-card-footer" style="display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem;">
                <div class="vx-text-muted">
                    Showing {{ $paginator->firstItem() ?: 0 }} to {{ $paginator->lastItem() ?: 0 }} of {{ $paginator->total() }} entries
                </div>
                <div>
                    {{ $paginator->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
