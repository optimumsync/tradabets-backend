@extends('_layouts.auth')

@section('main-content')

    <!-- start: page -->
    <section class="body-sign">
        <div class="center-sign">
            <a href="/" class="logo float-left">
                <img src="/themes/admin/img/logo-placeholder.png" height="54" alt="{{ env('APP_NAME') }}" />
            </a>

            <div class="panel card-sign">
                <div class="card-title-sign mt-3 text-right">
                    <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Sign In</h2>
                </div>
                <div class="card-body">

                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @include('_includes/errors')

                    {!! Form::open(['url' => '/login', 'class' => '']) !!}
                    

                        <div class="form-group mb-3">
                            <label>E-Mail Address</label>
                            <div class="input-group">
                                {{ Form::text('email', old('user_name'), ['class' => 'form-control form-control-lg', 'required' => true, 'autofocus' => true, 'tabindex' => 1]) }}
                                <span class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <div class="clearfix">
                                <label class="float-left">Password</label>
                                <a href="/password/reset" class="float-right">Lost Password?</a>
                            </div>
                            <div class="input-group">
                                {{ Form::password('password', ['class' => 'form-control form-control-lg', 'required' => true, 'tabindex' => 2]) }}
                                <span class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-eye-slash is-show-password-icon hover-icon-cursor-pointer"></i>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="row">
                            
                            <div class="col-sm-4 ">
                                <button type="submit" class="btn btn-primary mt-2">Login</button>
                            </div>
                             <a href="{{ url('auth/google') }}" class="btn btn-lg btn-primary btn-block">
          <strong>Login With Google</strong>
          </a> 
                        </div>

                        <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                            <span>or</span>
                        </span>

                        <p class="text-center">Don't have an account yet? <a href="{{ route('register') }}">Sign Up!</a></p>

                    {!! Form::close() !!}

                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">&copy; Copyright {{ date("Y") }}. All Rights Reserved.</p>
        </div>
    </section>
    <!-- end: page -->

@endsection
