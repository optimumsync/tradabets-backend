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
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">
            Details for {{ $user->first_name }} {{ $user->last_name }}
        </h2>
        <a href="{{ route('users.list') }}" class="btn btn-default"><i class="fas fa-arrow-left mr-2"></i> Back to User
            List</a>
    </header>
    <div class="card-body">
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
        <div class="tab-navigation-wrapper">
            <ul class="nav nav-tabs professional-tabs" id="userTabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#player-details">Player
                        Details</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#bet-history">Bet History</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#payment-history">Payment History</a>
                </li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#login-history">Login History</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#manage-bonus">Manage Bonus</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#bank-details">Bank Details</a></li>
            </ul>
        </div>

        <div class="tab-content py-0" id="userTabsContent">

            <div class="tab-pane fade show active" id="player-details" role="tabpanel">
                @include('user-list.partials._player-info', ['user' => $user])
            </div>

            <div class="tab-pane fade" id="bet-history" role="tabpanel">
                @include('user-list.partials._bet-history')
            </div>

            <div class="tab-pane fade" id="payment-history" role="tabpanel">
                {{-- MODIFIED: Add the $allTransactions variable here --}}
                @include('user-list.partials._payment-history', [
                'user' => $user,
                'allTransactions' => $allTransactions
                ])
            </div>

            <div class="tab-pane fade" id="login-history" role="tabpanel">
                @include('user-list.partials._login-history')
            </div>

            <div class="tab-pane fade" id="manage-bonus" role="tabpanel">
                @include('user-list.partials._manage-bonus')
            </div>

            <div class="tab-pane fade" id="bank-details" role="tabpanel">
                @include('user-list.partials._bank-details', ['user' => $user])
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