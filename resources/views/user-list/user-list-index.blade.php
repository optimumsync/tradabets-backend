@extends('_layouts.master')

@section('main-title', 'User Management')

@section('main-content')

{{-- CSS for the new attractive user card design --}}
<style>
.user-card-v2 {
    border-radius: 15px;
    box-shadow: 0 10px 25px -10px rgba(0, 0, 0, 0.15);
    border: none;
    overflow: hidden;
    /* Important for containing the banner */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
    /* For positioning the toggle switch */
}

.user-card-v2:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.2);
}

/* Top banner section */
.card-header-banner {
    background: #f6f6f6;
    /* Fallback color from theme */
    background: linear-gradient(135deg, #ecedf0 0%, #fdfdfd 100%);
    height: 100px;
}

.card-body.text-center {
    padding-top: 0;
}

/* Avatar styling and positioning */
.profile-image-wrapper {
    margin-top: -50px;
    /* Pulls the avatar up over the banner */
    margin-bottom: 1rem;
}

.profile-image {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background-color: #CCC;
    /* Theme primary color */
    color: #FFF;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 600;
    border: 4px solid #FFF;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin: 0 auto;
}

/* User info text */
.card-user-info .name {
    font-size: 1.25rem;
    font-weight: 600;
    color: #33353F;
    /* From theme */
    margin-bottom: 0.25rem;
}

.card-user-info .email {
    color: #777;
    /* From theme */
    font-size: 0.9rem;
}

/* Stats section (Role, Member Since) */
.user-stats {
    display: flex;
    justify-content: space-around;
    padding: 1rem 0;
    margin: 1rem 0;
    border-top: 1px solid #ecedf0;
    /* From theme */
    border-bottom: 1px solid #ecedf0;
    /* From theme */
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

/* Positioning the toggle switch */
.card-status-toggle {
    position: absolute;
    top: 15px;
    right: 15px;
    z-index: 10;
}
</style>

<div class="row">
    @forelse($user_list as $user)
    <div class="col-md-6 col-xl-4">
        <div class="card card-admin user-card-v2 mb-4">

            <div class="card-status-toggle">
                <div class="switch switch-sm switch-primary">
                    <input type="checkbox" name="switch" id="switch-{{ $user->id }}" data-plugin-ios-switch
                        checked="checked" />
                </div>
            </div>

            <div class="card-header-banner"></div>

            <div class="card-body text-center">
                <div class="profile-image-wrapper">
                    <div class="profile-image">
                        <span>{{ strtoupper(substr($user->first_name, 0, 1)) }}</span>
                    </div>
                </div>

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

                <div class="btn-group-vertical" style="width: 100%;">
                    <a href="#modal-details-{{ $user->id }}" class="btn btn-primary modal-trigger"><i
                            class="fas fa-eye mr-2"></i> View Full Details</a>
                    <a href="#" class="btn btn-default"><i class="fas fa-university mr-2"></i> Bank Details</a>
                    <a href="#" class="btn btn-default"><i class="fas fa-gift mr-2"></i> Add Bonus</a>
                    <a href="#" class="btn btn-default"><i class="fas fa-key mr-2"></i> Reset Password</a>
                </div>
            </div>
        </div>

        {{-- Modal - This part remains the same as it's correctly styled by your theme --}}
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

                        <dt class="col-sm-4 text-sm-right">Date of Birth:</dt>
                        <dd class="col-sm-8">
                            {{ $user->date_of_birth ? \Carbon\Carbon::parse($user->date_of_birth)->format('F j, Y') : 'Not provided' }}
                        </dd>

                        <dt class="col-sm-4 text-sm-right">Address:</dt>
                        <dd class="col-sm-8">
                            {{ $user->city ? $user->city . ', ' . $user->state . ', ' . $user->country : 'Not provided' }}
                        </dd>

                        <dt class="col-sm-4 text-sm-right">User Role:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-primary text-capitalize">{{ $user->role }}</span>
                        </dd>

                        <dt class="col-sm-4 text-sm-right">Member Since:</dt>
                        <dd class="col-sm-8">{{ $user->created_at->format('F j, Y \a\t H:i A') }}</dd>
                    </dl>
                </div>
                <footer class="card-footer text-right">
                    <button class="btn btn-default modal-dismiss">
                        <i class="fas fa-times"></i> Close
                    </button>
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