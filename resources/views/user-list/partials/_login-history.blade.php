<div class="table-responsive pt-3">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th>Login Time</th>
                <th>IP Address</th>
                <th>Device / Browser</th>
            </tr>
        </thead>
        <tbody>
            @forelse($user->loginHistories as $history)
            <tr>
                <td>{{ $history->login_at->format('F j, Y, g:i A') }}</td>
                <td>{{ $history->ip_address }}</td>
                {{-- MODIFIED: Replaced Str::limit() with a standard PHP function --}}
                <td>{{ (strlen($history->user_agent) > 80) ? substr($history->user_agent, 0, 80) . '...' : $history->user_agent }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center text-muted">No login history found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>