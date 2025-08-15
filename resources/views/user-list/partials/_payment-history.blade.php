{{-- Add specific styles for this partial --}}
<style>
.payment-history-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 1rem;
}
</style>

{{-- Sub-navigation for Payment History --}}
<div class="tab-navigation-wrapper">
    <ul class="nav nav-tabs professional-tabs" id="paymentTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="all-transactions-tab" data-toggle="tab" href="#all-transactions"
                role="tab">All</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="deposit-history-tab" data-toggle="tab" href="#deposit-history"
                role="tab">Deposit</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" id="withdrawal-history-tab" data-toggle="tab" href="#withdrawal-history"
                role="tab">Withdrawal</a>
        </li>
    </ul>
</div>

<div class="tab-content py-0" id="paymentTabsContent">
    {{-- 1. All Transactions Tab --}}
    <div class="tab-pane fade show active" id="all-transactions" role="tabpanel">
        <div class="table-responsive pt-3">
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($allTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->amount }}</td>
                        <td>
                            @if($transaction->transaction_type === 'Deposit')
                            <span class="badge badge-info">Deposit</span>
                            @else
                            <span class="badge badge-primary">Withdrawal</span>
                            @endif
                        </td>
                        <td>
                            @php
                            $status = strtolower($transaction->status);
                            $badgeClass = 'badge-secondary';
                            if (in_array($status, ['approved', 'success', 'deposit'])) { $badgeClass = 'badge-success';
                            }
                            elseif ($status === 'pending') { $badgeClass = 'badge-warning'; }
                            elseif (in_array($status, ['failed', 'rejected'])) { $badgeClass = 'badge-danger'; }
                            @endphp
                            <span class="badge {{ $badgeClass }} text-capitalize">{{ $transaction->status }}</span>
                        </td>
                        <td>{{ $transaction->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">No transaction records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 2. Deposit History Tab --}}
    <div class="tab-pane fade" id="deposit-history" role="tabpanel">
        <div class="payment-history-header">
            <h5>Deposits</h5>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary deposit-filter-btn active"
                    data-status="all">All</button>
                <button type="button" class="btn btn-outline-secondary deposit-filter-btn"
                    data-status="success">Success</button>
                <button type="button" class="btn btn-outline-secondary deposit-filter-btn"
                    data-status="failed">Failed</button>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-hover table-striped" id="deposit-table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->deposits as $deposit)
                    <tr data-status="{{ strtolower($deposit->status) }}">
                        <td>{{ $deposit->amount }}</td>
                        <td>
                            @php
                            $status = strtolower($deposit->status);
                            $badgeClass = 'status-badge';
                            if ($status === 'success' || $status === 'deposit') { $badgeClass .= ' success'; }
                            elseif ($status === 'failed' || $status === 'rejected') { $badgeClass .= ' failed'; }
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $deposit->status }}</span>
                        </td>
                        <td>{{ $deposit->created_at->format('Y-m-d H:i:s') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No deposit records found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. Withdrawal History Tab --}}
    <div class="tab-pane fade" id="withdrawal-history" role="tabpanel">
        <div class="payment-history-header">
            <h5>Withdrawals</h5>
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="btn btn-outline-secondary withdrawal-filter-btn active"
                    data-status="all">All</button>
                <button type="button" class="btn btn-outline-secondary withdrawal-filter-btn"
                    data-status="pending">Pending</button>
                <button type="button" class="btn btn-outline-secondary withdrawal-filter-btn"
                    data-status="approved">Approved</button>
                <button type="button" class="btn btn-outline-secondary withdrawal-filter-btn"
                    data-status="rejected">Rejected</button>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-hover table-striped" id="withdrawal-table">
                <thead>
                    <tr>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($user->withdrawals as $withdrawal)
                    <tr data-status="{{ strtolower($withdrawal->status) }}">
                        <td>{{ $withdrawal->amount }}</td>
                        <td>
                            <span
                                class="status-badge {{ strtolower($withdrawal->status) }}">{{ $withdrawal->status }}</span>
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
    </div>
</div>

{{-- This script should be included via @push, but for a partial, it can be here --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Deposit Filter Logic
    const depositFilterBtns = document.querySelectorAll('.deposit-filter-btn');
    const depositTableRows = document.querySelectorAll('#deposit-table tbody tr');

    depositFilterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Manage active button state
            depositFilterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const status = this.getAttribute('data-status');

            depositTableRows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = ''; // Show row
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });
        });
    });

    // Withdrawal Filter Logic
    const withdrawalFilterBtns = document.querySelectorAll('.withdrawal-filter-btn');
    const withdrawalTableRows = document.querySelectorAll('#withdrawal-table tbody tr');

    withdrawalFilterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            // Manage active button state
            withdrawalFilterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            const status = this.getAttribute('data-status');

            withdrawalTableRows.forEach(row => {
                if (status === 'all' || row.getAttribute('data-status') === status) {
                    row.style.display = ''; // Show row
                } else {
                    row.style.display = 'none'; // Hide row
                }
            });
        });
    });
});
</script>