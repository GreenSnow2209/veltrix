<div class="vx-card">
    <div class="vx-card-header">
        <h3 class="vx-card-title">Environment & Runtime</h3>
    </div>
    <div class="vx-card-body" style="padding: 0;">
        <table class="vx-table" style="margin: 0;">
            <tbody>
                @foreach($envs as $env)
                    <tr>
                        <td style="font-weight: 500; width: 140px; color: var(--vx-text-muted);">{{ $env['name'] }}</td>
                        <td><code class="vx-code-inline">{{ $env['value'] }}</code></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
