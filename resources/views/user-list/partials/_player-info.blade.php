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
                        <div class="col-md-8 detail-value">{{ $user->first_name }} {{ $user->last_name }}</div>
                    </div>
                    <div class="row detail-item">
                        <div class="col-md-4 detail-label"><i class="fas fa-envelope"></i> Email Address</div>
                        <div class="col-md-8 detail-value">{{ $user->email }}</div>
                    </div>
                    <div class="row detail-item">
                        <div class="col-md-4 detail-label"><i class="fas fa-phone"></i> Phone Number</div>
                        <div class="col-md-8 detail-value">{{ $user->country_code }} {{ $user->phone }}</div>
                    </div>
                    <div class="row detail-item">
                        <div class="col-md-4 detail-label"><i class="fas fa-user-shield"></i> User Role</div>
                        <div class="col-md-8 detail-value"><span
                                class="badge badge-primary text-capitalize">{{ $user->role }}</span></div>
                    </div>
                    <div class="row detail-item">
                        <div class="col-md-4 detail-label"><i class="fas fa-clock"></i> Member Since</div>
                        <div class="col-md-8 detail-value">{{ $user->created_at->format('F j, Y, g:i A') }}</div>
                    </div>
                </div>
                <div class="col-lg-4 d-flex align-items-center justify-content-center"
                    style="background-color: #f8f9fa; border-radius: 0.25rem;">
                    <div class="text-center p-3">
                        <h5 class="detail-section-title">Management</h5>
                        <p class="text-muted small">Permanently reset the user's password. This action cannot be undone.
                        </p>
                        <a href="{{ route('admin.users.send_reset_link', $user) }}" class="btn btn-danger mt-2"
                            onclick="return confirm('Are you sure you want to send a password reset link to {{ $user->first_name }}?');">
                            <i class="fas fa-key mr-2"></i> Send Reset Password Link
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>