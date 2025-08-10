@extends('_layouts.master')

@section('main-title', 'User Management')

@section('main-content')

{{-- CSS for the attractive user card design --}}
<style>
.user-card-v2 {
    border-radius: 15px;
    box-shadow: 0 10px 25px -10px rgba(0, 0, 0, 0.15);
    border: none;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}

.user-card-v2:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.2);
}

/* REMOVED: Banner styles are no longer needed for this compact design */

.user-card-v2 .card-body {
    padding-top: 45px;
    /* Added padding to prevent content from going under the toggle */
}

/* REMOVED: Avatar styles are no longer needed */

.card-user-info .name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #33353F;
    margin-bottom: 0.25rem;
}

.card-user-info .email {
    color: #777;
    /* MODIFIED: Reduced font size for the email address */
    font-size: 0.8rem;
    /* Ensures long text breaks to prevent overflowing the card */
    overflow-wrap: break-word;
    word-break: break-word;
}

.user-stats {
    display: flex;
    justify-content: space-around;
    /* Reduced vertical padding to decrease card height */
    padding: 0.75rem 0;
    margin: 1rem 0;
    border-top: 1px solid #ecedf0;
    border-bottom: 1px solid #ecedf0;
}

.user-stats .stat {
    text-align: center;
}

.user-stats .stat-value {
    font-size: 1rem;
    font-weight: 600;
    color: #33353F;
}

.user-stats .stat-label {
    font-size: 0.75rem;
    color: #777;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.card-status-toggle {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 10;
}
</style>

<div class="row">
    @forelse($user_list as $user)
    {{-- MODIFIED: Reduced bottom margin from mb-3 to mb-2 to further decrease the gap between rows --}}
    <div class="col-md-6 col-lg-4 col-xl-3 mb-1">
        <div class="card card-admin user-card-v2">

            <div class="card-status-toggle">
                <div class="switch switch-sm switch-primary">
                    <input type="checkbox" name="switch" id="switch-{{ $user->id }}" data-plugin-ios-switch
                        checked="checked" />
                </div>
            </div>

            <div class="card-body">
                {{-- The profile image has been removed as per the previous request --}}

                <div class="card-user-info">
                    <h4 class="name">{{ $user->first_name }} {{ $user->last_name }}</h4>
                    <p class="email"><i class="fas fa-envelope mr-1"></i> {{ $user->email ?? 'N/A' }}</p>
                </div>

                <div class="user-stats">
                    <div class="stat">
                        <div class="stat-value"><span
                                class="badge badge-primary text-capitalize p-2">{{ $user->role }}</span></div>
                        <div class="stat-label">Role</div>
                    </div>
                    <div class="stat">
                        <div class="stat-value">{{ $user->created_at->format('M j, Y') }}</div>
                        <div class="stat-label">Member Since</div>
                    </div>
                </div>

                {{-- Reduced top margin to mt-2 and changed to smaller buttons (btn-sm) --}}
                <div class="btn-group-vertical mt-1" style="width: 100%;">
                    <a href="#modal-details-{{ $user->id }}" class="btn btn-sm btn-primary modal-trigger"><i
                            class="fas fa-eye mr-2"></i> View Full Details</a>
                    <a href="#modal-bank-details-{{ $user->id }}" class="btn btn-sm btn-default modal-trigger"><i
                            class="fas fa-university mr-2"></i> Bank Details</a>
                    <a href="#" class="btn btn-sm btn-default"><i class="fas fa-gift mr-2"></i> Add Bonus</a>
                    <a href="#" class="btn btn-sm btn-default"><i class="fas fa-key mr-2"></i> Reset Password</a>
                </div>
            </div>
        </div>

        {{-- User Details Modal --}}
        <div id="modal-details-{{ $user->id }}" class="modal-block modal-block-primary mfp-hide">
            <section class="card card-admin">
                <header class="card-header">
                    <h2 class="card-title">
                        <i class="fas fa-user-circle"></i>
                        Details for {{ $user->first_name }} {{ $user->last_name }}
                    </h2>
                </header>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4 text-sm-right">User ID:</dt>
                        <dd class="col-sm-8">#{{ $user->id }}</dd>
                        <dt class="col-sm-4 text-sm-right">Full Name:</dt>
                        <dd class="col-sm-8">{{ $user->first_name }} {{ $user->last_name }}</dd>
                        <dt class="col-sm-4 text-sm-right">Email Address:</dt>
                        <dd class="col-sm-8">{{ $user->email }}</dd>
                        <dt class="col-sm-4 text-sm-right">Phone Number:</dt>
                        <dd class="col-sm-8">{{ $user->country_code }} {{ $user->phone }}</dd>
                        <dt class="col-sm-4 text-sm-right">User Role:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-primary text-capitalize">{{ $user->role }}</span>
                        </dd>
                        <dt class="col-sm-4 text-sm-right">Member Since:</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('F j, Y \a\t H:i A') }}</dd>
                    </dl>
                </div>
                <footer class="card-footer text-right">
                    <button class="btn btn-default modal-dismiss">Close</button>
                </footer>
            </section>
        </div>

        {{-- Bank Details Modal --}}
        <div id="modal-bank-details-{{ $user->id }}" class="modal-block modal-block-primary mfp-hide">
            <section class="card card-admin">
                {{-- MODIFIED: Added flexbox classes to space out the title and the new button --}}
                <header class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title mb-0">
                        <i class="fas fa-university"></i>
                        Bank Accounts for {{ $user->first_name }}
                    </h2>
                    {{-- ADDED: "Add Bank Account" button --}}
                    <a href="#" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Add Bank Account
                    </a>
                </header>
                {{-- MODIFIED: Added flexbox classes to center the content vertically and horizontally --}}
                <div class="card-body d-flex flex-column justify-content-center">
                    @forelse($user->userBankDetails as $bankAccount)
                    {{-- MODIFIED: Wrapped each entry in a div to manage width and alignment --}}
                    <div class="bank-account-entry w-100">
                        <dl class="row">
                            <dt class="col-sm-4 text-sm-right">Status:</dt>
                            <dd class="col-sm-8">
                                @if($bankAccount->Active_status == 'Active')
                                <span class="badge badge-success">Active</span>
                                @else
                                <span class="badge badge-dark">Inactive</span>
                                @endif
                            </dd>
                            <dt class="col-sm-4 text-sm-right">Bank Name:</dt>
                            <dd class="col-sm-8">{{ $bankAccount->bank_name }}</dd>
                            <dt class="col-sm-4 text-sm-right">Account Name:</dt>
                            <dd class="col-sm-8">{{ $bankAccount->account_name }}</dd>
                            <dt class="col-sm-4 text-sm-right">Account Number:</dt>
                            <dd class="col-sm-8 font-weight-bold">{{ $bankAccount->account_number }}</dd>
                            <dt class="col-sm-4 text-sm-right">Added On:</dt>
                            <dd class="col-sm-8">{{ $bankAccount->created_at->format('M j, Y') }}</dd>
                        </dl>
                    </div>

                    {{-- Add a separator line if this is NOT the last account --}}
                    @if(!$loop->last)
                    <hr class="my-2">
                    @endif

                    @empty
                    <div class="text-center">
                        <p class="text-muted">No bank accounts have been added for this user.</p>
                    </div>
                    @endforelse
                </div>
                <footer class="card-footer text-right">
                    <button class="btn btn-default modal-dismiss">Close</button>
                </footer>
            </section>
        </div>
    </div>
    @empty
    {{-- Empty State Message --}}
    <div class="col-12">
        <section class="card card-admin">
            <div class="card-body text-center" style="padding: 4rem 2rem;">
                <i class="fas fa-users" style="font-size: 3rem; color: #9ca3af;"></i>
                <h4 class="text-muted mt-3">No users found</h4>
                <p class="text-muted mt-2">There are currently no users to display.</p>
            </div>
        </section>
    </div>
    @endforelse
</div>
@endsection

@push('scripts')
{{-- The JavaScript does not need to be changed --}}
<script>
$(document).ready(function() {
    $('.modal-trigger').magnificPopup({
        type: 'inline',
        preloader: false,
        modal: true,
        callbacks: {
            open: function() {
                $(this.content).parent().addClass('card-admin');
            }
        }
    });

    $(document).on('click', '.modal-dismiss', function(e) {
        e.preventDefault();
        $.magnificPopup.close();
    });

    $(document).on('change', '.switch input[type="checkbox"]', function() {
        const isChecked = $(this).is(':checked');
        const userId = $(this).attr('id').replace('switch-', '');
        console.log('User ' + userId + ' status changed to: ' + (isChecked ? 'active' : 'inactive'));
    });
});
</script>
@endpush