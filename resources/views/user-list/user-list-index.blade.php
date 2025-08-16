@extends('_layouts.master')

@section('main-content')

{{-- CSS for the user cards --}}
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

.user-card-v2 .card-body {
    padding-top: 45px;
}

.card-user-info .name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #33353F;
    margin-bottom: 0.25rem;
}

.card-user-info .email {
    color: #777;
    font-size: 0.8rem;
    overflow-wrap: break-word;
    word-break: break-word;
}

.user-stats {
    display: flex;
    justify-content: space-around;
    padding: 0.6rem 0;
    margin: 0.8rem 0;
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

<section class="card card-admin">
    <header class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">User Management</h2>
        <div>
            <a href="{{ url('kyc-upload-form') }}" class="btn btn-primary">Add KYC of User</a>
            <a href="{{ route('users.export.csv') }}{{ request()->has('search') ? '?search='.request('search') : '' }}"
                class="btn btn-success">
                <i class="fas fa-download mr-2"></i> Export All Users
            </a>
        </div>
    </header>

    <div class="card-body">

        {{-- START: RESTORED SECTION for filtering and search --}}
        <form id="user-filter-form" action="{{ route('users.list') }}" method="GET">
            <div class="row align-items-center justify-content-between mb-4">
                <div class="col-md-4 col-lg-3">
                    <div class="dataTables_length" id="datatable-default_length">
                        <label class="d-flex align-items-center">
                            <select name="per_page" id="per_page_select" class="form-control form-control-sm">
                                <option value="24" {{ request('per_page', 24) == 24 ? 'selected' : '' }}>24</option>
                                <option value="48" {{ request('per_page') == 48 ? 'selected' : '' }}>48</option>
                                <option value="96" {{ request('per_page') == 96 ? 'selected' : '' }}>96</option>
                                <option value="192" {{ request('per_page') == 192 ? 'selected' : '' }}>192</option>
                            </select>
                            <span class="ml-2 text-nowrap">records per page</span>
                        </label>
                    </div>
                </div>
                <div class="col-md-5 col-lg-4">
                    <div id="datatable-default_filter" class="dataTables_filter">
                        <label class="d-flex align-items-center">
                            <span class="mr-2">Search:</span>
                            <input type="search" name="search" class="form-control form-control-sm"
                                placeholder="Search by name, email..." value="{{ request('search') }}"
                                aria-controls="datatable-default">
                        </label>
                    </div>
                </div>
            </div>
        </form>
        {{-- END: RESTORED SECTION --}}

        <div class="row">
            @forelse($user_list as $user)
            <div class="col-6 col-md-6 col-lg-4 col-xl-3 mb-3">
                <div class="card card-admin user-card-v2">
                    @if ($user->role !== 'admin')
                    <div class="card-status-toggle">
                        <div class="switch switch-sm switch-primary">
                            <input type="checkbox" name="switch" class="user-status-toggle"
                                data-user-id="{{ $user->id }}" data-plugin-ios-switch
                                {{ $user->is_active ? 'checked' : '' }} />
                        </div>
                    </div>
                    @endif
                    <div class="card-body">
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
                        <div class="btn-group-vertical mt-3" style="width: 100%;">
                            {{-- Button now links to the new details route --}}
                            <a href="{{ route('users.details', ['id' => $user->id]) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye mr-2"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="card card-admin">
                    <div class="card-body text-center" style="padding: 4rem 2rem;">
                        <i class="fas fa-users" style="font-size: 3rem; color: #9ca3af;"></i>
                        <h4 class="text-muted mt-3">No Users Found</h4>
                        @if(request('search'))
                        <p class="text-muted mt-2">
                            Your search for "{{ request('search') }}" did not match any users.
                        </p>
                        @endif
                    </div>
                </div>
            </div>
            @endforelse
        </div>

        {{-- Pagination and Record Info --}}
        @if ($user_list->total() > 0)
        <div class="row mt-3 align-items-center">
            <div class="col-sm-12 col-md-5">
                <div class="dataTables_info" role="status" aria-live="polite">
                    Showing {{ $user_list->firstItem() }} to {{ $user_list->lastItem() }} of {{ $user_list->total() }}
                    entries
                </div>
            </div>
            <div class="col-sm-12 col-md-7">
                {{-- Only show page links if there is more than one page --}}
                @if ($user_list->hasPages())
                <div class="dataTables_paginate paging_simple_numbers d-flex justify-content-end">
                    {{ $user_list->links() }}
                </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // MODIFIED: Reverted to standard browser confirm() and alert()
    $('.user-status-toggle').on('change', function() {
        const toggleSwitch = $(this);
        const userId = toggleSwitch.data('user-id');
        const url = `/admin/users/${userId}/toggle-status`;

        const intendedStatusIsActive = toggleSwitch.is(':checked');
        const actionText = intendedStatusIsActive ? 'activate' : 'deactivate';

        // Step 1: Show the browser's confirmation dialog
        if (confirm(`Are you sure you want to ${actionText} this user's account?`)) {
            // Step 2: If confirmed, make the AJAX call
            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    'is_active': intendedStatusIsActive ? 1 : 0,
                    '_token': '{{ csrf_token() }}'
                },
                error: function(xhr) {
                    // Revert the switch on request failure and show error
                    toggleSwitch.prop('checked', !intendedStatusIsActive);
                    toggleSwitch.data('plugin-ios-switch').destroy().iosSwitch();

                    let errorMessage = 'An unexpected error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    alert(errorMessage);
                    console.error(xhr.responseText);
                }
            });
        } else {
            // Step 3: If canceled, revert the switch's visual state
            toggleSwitch.prop('checked', !intendedStatusIsActive);
            toggleSwitch.data('plugin-ios-switch').destroy().iosSwitch();
        }
    });

    // Auto-submit the filter form
    $('#per_page_select').on('change', function() {
        $('#user-filter-form').submit();
    });
});
</script>
@endpush