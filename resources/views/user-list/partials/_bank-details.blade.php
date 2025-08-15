<div class="d-flex justify-content-end mb-3 pt-3">
    <a href="{{ url('/admin/add-user-bank-account?user_id=' . $user->id) }}" class="btn btn-primary btn-sm">
        <i class="fas fa-plus mr-1"></i> Add New Bank Account
    </a>
</div>
<div class="row">
    @forelse($user->userBankDetails as $bankAccount)
    <div class="col-md-4">
        <div class="bank-account-card {{ $bankAccount->Active_status == 'Active' ? 'active-card' : '' }}">
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