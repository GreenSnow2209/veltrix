<div class="vx-card">
    <div class="vx-card-header">
        <h3 class="vx-card-title">Record Details</h3>
        <div style="display: flex; gap: 8px;">
            <a href="{{ request()->url() }}/edit" class="vx-btn vx-btn-primary vx-btn-sm">Edit</a>
            <a href="{{ dirname(request()->url()) }}" class="vx-btn vx-btn-subtle vx-btn-sm">&larr; Back to List</a>
        </div>
    </div>

    <div class="vx-card-body" style="padding: 0;">
        <table class="vx-table" style="margin: 0;">
            <tbody>
                @foreach($fields as $field)
                    <tr>
                        <td style="width: 220px; font-weight: 600; color: var(--vx-text-muted); background: rgba(0, 0, 0, 0.015);">
                            {{ $field['label'] }}
                        </td>
                        <td>
                            {!! $field['value'] !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
