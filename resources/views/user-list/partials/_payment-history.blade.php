<h5 class="pt-3">Withdrawals</h5>
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($user->withdrawals as $withdrawal)
            <tr>
                <td>{{ $withdrawal->amount }}</td>
                <td><span class="status-badge {{ strtolower($withdrawal->status) }}">{{ $withdrawal->status }}</span>
                </td>
                <td>{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="text-center text-muted">No withdrawal records found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
<h5 class="mt-4">Deposits</h5>
<div class="table-responsive">
    <table class="table table-hover table-striped">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="3" class="text-center text-muted">No deposit records found.</td>
            </tr>
        </tbody>
    </table>
</div>