@extends('_layouts.auth')

@section('main-content')

    <!-- start: page -->
    <section class="body-sign">
        <div class="center-sign">
            <a href="/" class="logo float-left">
                <img src="/themes/admin/img/logo.png" height="54" alt="{{ env('APP_NAME') }}" />
            </a>

            <div class="panel card-sign">
                <div class="card-title-sign mt-3 text-right">
                    <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Verify E-Mail Account</h2>
                </div>
                <div class="card-body">

                    @if (session('resent'))
                        <div class="alert alert-success" role="alert">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                    @endif

                    <p>{{ __('Before proceeding, please check your e-mail for a verification link.') }}</p>
                    <p>{{ __('If you did not receive the email') }}, <a href="{{ route('verification.resend') }}">{{ __('click here to request another') }}</a>.</p>

                    <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                        <span>or</span>
                    </span>

                    <p class="text-center mt-3">Verified? <a href="/login">Log In!</a></p>

                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">&copy; Copyright {{ date("Y") }}. All Rights Reserved.</p>
        </div>
    </section>
    <!-- end: page -->

@endsection
