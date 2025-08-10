@extends('_layouts.master')

@section('main-title', 'Admin: Add Bank Account')

{{-- Add Select2 CSS to the header --}}
@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
/* Ensures the Select2 dropdown appears correctly within Bootstrap's layout */
.select2-container .select2-selection--single {
    height: 38px;
}

.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
}

.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>
@endpush


@section('main-content')

{{-- Display Feedback Messages --}}
@if ($errors->any())
<div class="alert alert-danger">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    <ul class="mb-0">@foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach</ul>
</div>
@endif

@if (session('error'))
<div class="alert alert-danger">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    {{ session('error') }}
</div>
@endif

{{-- START: Added block to display the success message from the controller --}}
@if (session('success'))
<div class="alert alert-success">
    <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
    {{ session('success') }}
</div>
@endif
{{-- END: Added block --}}


<section class="card">
    <header class="card-header">
        <h2 class="card-title">Admin: Add Bank Account for User</h2>
    </header>

    <form action="{{ url('/admin/add-user-bank-account') }}" method="POST" class="form-horizontal">
        @csrf
        <div class="card-body">

            {{-- User Selection --}}
            <div class="form-group row">
                <label for="user_id" class="col-sm-3 text-sm-right control-label">Select User <span
                        class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select id="user_id" name="user_id" class="form-control" required>
                        <option value="">-- Select a User --</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Account Name --}}
            <div class="form-group row">
                <label for="account_name" class="col-sm-3 text-sm-right control-label">Account Name <span
                        class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="text" id="account_name" name="account_name" class="form-control"
                        value="{{ old('account_name') }}" required>
                </div>
            </div>

            {{-- Account Number --}}
            <div class="form-group row">
                <label for="account_number" class="col-sm-3 text-sm-right control-label">Account Number <span
                        class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <input type="text" id="account_number" name="account_number" class="form-control"
                        value="{{ old('account_number') }}" required maxlength="10">
                </div>
            </div>

            {{-- Bank Selection --}}
            <div class="form-group row">
                <label for="bank_id" class="col-sm-3 text-sm-right control-label">Bank <span
                        class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select id="bank_id" name="bank_id" class="form-control" required>
                        <option value="">-- Select a Bank --</option>
                        @foreach($banks as $bank)
                        <option value="{{ $bank->id }}" {{ old('bank_id') == $bank->id ? 'selected' : '' }}>
                            {{ $bank->bank_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Active Status --}}
            <div class="form-group row">
                <label for="Active_status" class="col-sm-3 text-sm-right control-label">Status <span
                        class="text-danger">*</span></label>
                <div class="col-sm-8">
                    <select id="Active_status" name="Active_status" class="form-control" required>
                        <option value="Active" {{ old('Active_status', 'Active') == 'Active' ? 'selected' : '' }}>Active
                        </option>
                        <option value="Inactive" {{ old('Active_status') == 'Inactive' ? 'selected' : '' }}>Inactive
                        </option>
                    </select>
                </div>
            </div>

            {{-- Recipient Code (Optional) --}}
            <div class="form-group row">
                <label for="recipient_code" class="col-sm-3 text-sm-right control-label">Recipient Code</label>
                <div class="col-sm-8">
                    <input type="text" id="recipient_code" name="recipient_code" class="form-control"
                        value="{{ old('recipient_code') }}">
                    <small class="form-text text-muted">Optional: Paystack recipient code, if available.</small>
                </div>
            </div>

            {{-- Number Type (Optional) --}}
            <div class="form-group row">
                <label for="num_type" class="col-sm-3 text-sm-right control-label">Number Type</label>
                <div class="col-sm-8">
                    <input type="text" id="num_type" name="num_type" class="form-control"
                        value="{{ old('num_type', 'nuban') }}">
                    <small class="form-text text-muted">Optional: Account type, defaults to 'nuban'.</small>
                </div>
            </div>

        </div>
        <footer class="card-footer text-right">
            <a href="{{ url('/bank-accounts') }}" class="btn btn-default">Cancel</a>
            <button type="submit" class="btn btn-primary">Save Account</button>
        </footer>
    </form>
</section>

@endsection

{{-- Add Select2 JS at the bottom --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 on the user and bank dropdowns
    // The dropdownParent option ensures the dropdown opens correctly
    // and is not trapped by other elements.
    $('#user_id').select2({
        placeholder: '-- Select a User --',
        dropdownParent: $('#user_id').parent()
    });

    $('#bank_id').select2({
        placeholder: '-- Select a Bank --',
        dropdownParent: $('#bank_id').parent()
    });
});
</script>
@endpush