@extends('_layouts.master')

@section('main-title', 'Verify')

@section('main-content')

    <section class="card">
        <div class="card-body">
            <div class="col-lg-6">

                <form method="post" action="{{ route('otp') }}">

                    <label>Verify OTP</label>
                    
                    <input type="number" class="form-control" name="otp" value="" placeholder="enter otp" required/><br>

                    <input type="submit" name="transfer" value="Verify" class="btn admin-reg-btn btn-block" />

                </form>

            </div>
        </div>
    </section>

@endsection
