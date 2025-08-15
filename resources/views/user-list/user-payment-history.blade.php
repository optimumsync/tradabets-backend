@extends('_layouts.master')

@section('main-content')

<style>
/* START: Tab Styles (Conflict-Proof) */
.tab-navigation-wrapper {
    background-color: #e9ecef;
    padding: 0.5rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
}

.professional-tabs {
    border-bottom: none;
    justify-content: flex-start;
}

.professional-tabs .nav-item {
    margin-right: 0.5rem;
}

.professional-tabs .nav-link {
    padding: 0.8rem 1.25rem;
    font-weight: 600;
    color: #000000 !important;
    background-color: #ffffff !important;
    border: none;
    border-radius: 8px;
    transition: all 0.3s ease-in-out;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
    white-space: nowrap;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.professional-tabs .nav-link:not(.active):hover {
    background-color: #f8f9fa !important;
    color: #000000 !important;
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

.professional-tabs .nav-link.active {
    background-color: #212529 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

/* END: Tab Styles */

/* General Styles for Tables, etc. */
.table-responsive {
    border: none;
    box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
    overflow-x: auto;
}

.table thead th {
    border-top: none;
    border-bottom: 2px solid #e9ecef;
    background-color: #f6f6f6;
}

.status-badge {
    font-weight: 600;
    padding: 0.4em 1em;
    border-radius: 999px;
    font-size: 0.8em;
    text-transform: uppercase;
    display: inline-block;
    min-width: 90px;
    text-align: center;
}

.status-badge.pending {
    background-color: #ffc107;
    color: #856404;
}

.status-badge.approved {
    background-color: #28a745;
    color: #fff;
}
.status-badge.rejected {
    background-color: #dc3545;
    color: #fff;
}
.status-badge.success {
    background-color: #28a745;
    color: #fff;
}
.status-badge.failed {
    background-color: #dc3545;
    color: #fff;
}
</style>

<section class="card card-admin">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">
            Payment History for {{ $user->first_name }} {{ $user->last_name }}
        </h2>
        <a href="{{ route('users.details', ['id' => $user->id]) }}" class="btn btn-default"><i class="fas fa-arrow-left mr-2"></i> Back to User Details</a>
    </header>
    <div class="card-body">
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
            {{-- All Transactions Tab Content --}}
            <div class="tab-pane fade show active" id="all-transactions" role="tabpanel">
                <h5 class="pt-3">All Transactions</h5>
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Type</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allTransactions as $transaction)
                            <tr>
                                <td>{{ $transaction->amount }}</td>
                                <td>
                                    @php
                                    $badgeClass = 'badge-secondary';
                                    if (in_array(strtolower($transaction->status), ['approved', 'success', 'deposit'])) {
                                        $badgeClass = 'badge-success';
                                    } elseif (strtolower($transaction->status) === 'pending') {
                                        $badgeClass = 'badge-warning';
                                    } elseif (in_array(strtolower($transaction->status), ['failed', 'rejected'])) {
                                        $badgeClass = 'badge-danger';
                                    }
                                    @endphp
                                    <span class="badge {{ $badgeClass }} text-capitalize p-2">{{ $transaction->status }}</span>
                                </td>
                                <td>{{ $transaction->transaction_type ?? 'N/A' }}</td>
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
            {{-- Deposit History Tab Content --}}
            <div class="tab-pane fade" id="deposit-history" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center pt-3">
                    <h5>Deposits</h5>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="depositFilterButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Filter: {{ ucfirst($depositStatus) }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="depositFilterButton">
                            <a class="dropdown-item" href="{{ route('users.payment.history', ['id' => $user->id, 'deposit_status' => 'All']) }}">All</a>
                            <a class="dropdown-item" href="{{ route('users.payment.history', ['id' => $user->id, 'deposit_status' => 'success']) }}">Success</a>
                            <a class="dropdown-item" href="{{ route('users.payment.history', ['id' => $user->id, 'deposit_status' => 'failed']) }}">Failed</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($deposits as $deposit)
                            <tr>
                                <td>{{ $deposit->amount }}</td>
                                <td>
                                    @php
                                        $status = strtolower($deposit->status);
                                        $badgeClass = 'status-badge';
                                        if ($status === 'success' || $status === 'deposit') {
                                            $badgeClass .= ' success';
                                        } elseif ($status === 'failed' || $status === 'rejected') {
                                            $badgeClass .= ' failed';
                                        }
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
            {{-- Withdrawal History Tab Content --}}
            <div class="tab-pane fade" id="withdrawal-history" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center pt-3">
                    <h5>Withdrawals</h5>
                    <div class="dropdown">
                        <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Filter: {{ ucfirst($withdrawalStatus) }}
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" href="{{ route('users.payment.history', ['id' => $user->id, 'withdrawal_status' => 'All']) }}">All</a>
                            <a class="dropdown-item" href="{{ route('users.payment.history', ['id' => $user->id, 'withdrawal_status' => 'pending']) }}">Pending</a>
                            <a class="dropdown-item" href="{{ route('users.payment.history', ['id' => $user->id, 'withdrawal_status' => 'approved']) }}">Approved</a>
                            <a class="dropdown-item" href="{{ route('users.payment.history', ['id' => $user->id, 'withdrawal_status' => 'rejected']) }}">Rejected</a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($withdrawals as $withdrawal)
                            <tr>
                                <td>{{ $withdrawal->amount }}</td>
                                <td><span
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
    </div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Function to get query parameter from URL
    function getQueryParam(param) {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(param);
    }

    const depositStatus = getQueryParam('deposit_status');
    const withdrawalStatus = getQueryParam('withdrawal_status');
    
    // Activate the correct sub-tab based on the URL parameter
    if (depositStatus) {
        // If a deposit filter is in the URL, activate the deposit tab
        $('#paymentTabs a[href="#deposit-history"]').tab('show');
    } else if (withdrawalStatus) {
        // If a withdrawal filter is in the URL, activate the withdrawal tab
        $('#paymentTabs a[href="#withdrawal-history"]').tab('show');
    } else {
        // Default to the All Transactions tab if no filter parameter is present
        $('#paymentTabs a[href="#all-transactions"]').tab('show');
    }
});
</script>
@endpush