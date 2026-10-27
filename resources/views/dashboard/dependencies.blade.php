<div class="vx-card">
    <div class="vx-card-header">
        <h3 class="vx-card-title">Installed Packages</h3>
    </div>
    <div class="vx-card-body" style="padding: 0; max-height: 380px; overflow-y: auto;">
        <table class="vx-table" style="margin: 0;">
            <tbody>
                @forelse($dependencies as $dep)
                    <tr>
                        <td style="font-weight: 500;">{{ $dep['name'] }}</td>
                        <td><span class="vx-badge vx-badge-info">{{ $dep['version'] }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="vx-text-muted" style="text-align: center; padding: 20px;">
                            Composer dependencies list
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
