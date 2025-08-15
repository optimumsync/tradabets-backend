@extends('_layouts.master')

@section('main-content')

{{-- Styles for the details page content --}}
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


/* START: Player Details Styles */
.player-details-container .detail-section-title {
    font-size: 1rem !important;
    font-weight: 700 !important;
    color: #343a40 !important;
    margin-bottom: 1rem !important;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.player-details-container .detail-item {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
    border-bottom: 1px solid #e9ecef !important;
    display: flex !important;
    align-items: center !important;
    margin-left: 0 !important;
    margin-right: 0 !important;
}

.player-details-container .detail-item:last-of-type {
    border-bottom: none !important;
}

.player-details-container .detail-label {
    color: #6c757d !important;
    font-weight: 600 !important;
    font-size: 0.85rem !important;
    display: flex !important;
    align-items: center !important;
}

.player-details-container .detail-label i {
    color: #007bff !important;
    font-size: 1.1rem !important;
    margin-right: 12px !important;
    width: 22px !important;
    text-align: center !important;
}

.player-details-container .detail-value {
    color: #212529 !important;
    font-weight: 500 !important;
    font-size: 1rem !important;
}

/* END: Player Details Styles */


/* START: Bank Details Styles */
.bank-account-card {
    background-color: #f8f9fa !important;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    margin-bottom: 1rem !important;
    padding: 1rem !important;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.bank-account-card.active-card {
    border-left: 5px solid #28a745 !important;
}

.bank-account-header {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 0.75rem !important;
    padding-bottom: 0.75rem !important;
    border-bottom: 1px solid #dee2e6 !important;
}

.bank-account-header .bank-name {
    font-size: 1.1rem !important;
    font-weight: 600 !important;
    color: #343a40 !important;
}

.bank-account-body {
    flex-grow: 1;
}

.bank-account-body .detail-pair {
    margin-bottom: 0.5rem !important;
}

.bank-account-body .detail-title {
    font-size: 0.75rem !important;
    color: #6c757d !important;
    text-transform: uppercase !important;
    margin-bottom: 0.1rem !important;
}

.bank-account-body .detail-info {
    font-size: 0.9rem !important;
    color: #212529 !important;
}

.bank-account-body .account-number {
    font-weight: 600 !important;
    letter-spacing: 1px;
}

/* END: Bank Details Styles */


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
</style>

<section class="card card-admin">
    @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    @if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">
            Details for {{ $user->first_name }} {{ $user->last_name }}
        </h2>
        <a href="{{ route('users.list') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-2"></i> Back to User
            List</a>
    </header>
    <div class="card-body">
        <div class="tab-navigation-wrapper">
            <ul class="nav nav-tabs professional-tabs" id="userTabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" id="player-details-tab" data-toggle="tab"
                        href="#player-details" role="tab">Player Details</a></li>
                <li class="nav-item"><a class="nav-link" id="bet-history-tab" data-toggle="tab" href="#bet-history"
                        role="tab">Bet History</a></li>
                <li class="nav-item"><a class="nav-link" id="payment-history-tab" data-toggle="tab"
                        href="#payment-history" role="tab">Payment History</a></li>
                <li class="nav-item"><a class="nav-link" id="login-history-tab" data-toggle="tab" href="#login-history"
                        role="tab">Login History</a></li>
                <li class="nav-item"><a class="nav-link" id="manage-bonus-tab" data-toggle="tab" href="#manage-bonus"
                        role="tab">Manage Bonus</a></li>
                <li class="nav-item"><a class="nav-link" id="bank-details-tab" data-toggle="tab" href="#bank-details"
                        role="tab">Bank Details</a></li>
            </ul>
        </div>

        <div class="tab-content py-0" id="userTabsContent">

            {{-- 1. Player Details Tab --}}
            <div class="tab-pane fade show active" id="player-details" role="tabpanel">
                <div class="player-details-container">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h5 class="detail-section-title">User Information</h5>
                                    <div class="row detail-item">
                                        <div class="col-md-4 detail-label"><i class="fas fa-id-card"></i> User ID</div>
                                        <div class="col-md-8 detail-value">#{{ $user->id }}</div>
                                    </div>
                                    <div class="row detail-item">
                                        <div class="col-md-4 detail-label"><i class="fas fa-user"></i> Full Name</div>
                                        <div class="col-md-8 detail-value">{{ $user->first_name }}
                                            {{ $user->last_name }}</div>
                                    </div>
                                    <div class="row detail-item">
                                        <div class="col-md-4 detail-label"><i class="fas fa-envelope"></i> Email Address
                                        </div>
                                        <div class="col-md-8 detail-value">{{ $user->email }}</div>
                                    </div>
                                    <div class="row detail-item">
                                        <div class="col-md-4 detail-label"><i class="fas fa-phone"></i> Phone Number
                                        </div>
                                        <div class="col-md-8 detail-value">{{ $user->country_code }} {{ $user->phone }}
                                        </div>
                                    </div>
                                    <div class="row detail-item">
                                        <div class="col-md-4 detail-label"><i class="fas fa-user-shield"></i> User Role
                                        </div>
                                        <div class="col-md-8 detail-value"><span
                                                class="badge badge-primary text-capitalize">{{ $user->role }}</span>
                                        </div>
                                    </div>
                                    <div class="row detail-item">
                                        <div class="col-md-4 detail-label"><i class="fas fa-clock"></i> Member Since
                                        </div>
                                        <div class="col-md-8 detail-value">
                                            {{ $user->created_at->format('F j, Y, g:i A') }}</div>
                                    </div>
                                </div>
                                <div class="col-lg-4 d-flex align-items-center justify-content-center"
                                    style="background-color: #f8f9fa; border-radius: 0.25rem;">
                                    <div class="text-center p-3">
                                        <h5 class="detail-section-title">Management</h5>
                                        <p class="text-muted small">Permanently reset the user's password. This action
                                            cannot be undone.</p>
                                        <a href="{{ route('admin.users.send_reset_link', $user) }}"
                                            class="btn btn-danger mt-2"
                                            onclick="return confirm('Are you sure you want to send a password reset link to {{ $user->first_name }}?');">
                                            <i class="fas fa-key mr-2"></i> Send Reset Password Link
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Bet History Tab --}}
            <div class="tab-pane fade" id="bet-history" role="tabpanel">
                <div class="text-center p-4">
                    <p class="text-muted">Betting history will be displayed here.</p>
                </div>
            </div>

            {{-- 3. Payment History Tab --}}
            <div class="tab-pane fade" id="payment-history" role="tabpanel">
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
            </div>

            {{-- 4. Login History Tab --}}
            <div class="tab-pane fade" id="login-history" role="tabpanel">
                <div class="text-center p-4">
                    <p class="text-muted">User login history will be displayed here.</p>
                </div>
            </div>

            {{-- 5. Manage Bonus Tab --}}
            <div class="tab-pane fade" id="manage-bonus" role="tabpanel">
                <div class="text-center p-4">
                    <p class="text-muted">Bonus management tools will be available here.</p>
                </div>
            </div>

            {{-- 6. Bank Details Tab --}}
            <div class="tab-pane fade" id="bank-details" role="tabpanel">
                <div class="d-flex justify-content-end mb-3 pt-3">
                    <a href="{{ url('/admin/add-user-bank-account?user_id=' . $user->id) }}"
                        class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Add New Bank Account
                    </a>
                </div>
                <div class="row">
                    @forelse($user->userBankDetails as $bankAccount)
                    {{-- MODIFIED: Changed col-md-6 to col-md-4 for a 3-column layout --}}
                    <div class="col-md-4">
                        <div
                            class="bank-account-card {{ $bankAccount->Active_status == 'Active' ? 'active-card' : '' }}">
                            <div class="bank-account-header">
                                <div class="bank-name"><i class="fas fa-university mr-2 text-muted"></i>
                                    {{ $bankAccount->bank_name }}</div>
                                @if($bankAccount->Active_status == 'Active')
                                <span class="badge badge-success">ACTIVE</span>
                                @else
                                <span class="badge badge-dark">INACTIVE</span>
                                @endif
                            </div>
                            <div class="bank-account-body">
                                <div class="detail-pair">
                                    <div class="detail-title">Account Holder Name</div>
                                    <div class="detail-info">{{ $bankAccount->account_name }}</div>
                                </div>
                                <div class="detail-pair">
                                    <div class="detail-title">Account Number</div>
                                    <div class="detail-info account-number">{{ $bankAccount->account_number }}</div>
                                </div>
                                <div class="detail-pair">
                                    <div class="detail-title">Date Added</div>
                                    <div class="detail-info">{{ $bankAccount->created_at->format('M j, Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center p-4">
                            <p class="text-muted mb-0">No bank accounts have been added for this user.</p>
                        </div>
                    </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
// Script to handle tab switching
$(document).ready(function() {
    $('#userTabs a').on('click', function(e) {
        e.preventDefault();
        $(this).tab('show');
    });
});
</script>
@endpush