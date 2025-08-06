@extends('_layouts.auth')

@section('main-content')

    <!-- start: page -->
    <section class="body-sign">
        <div class="center-sign">

            <div class="panel card-sign">
                <div class="card-title-sign mt-3 text-right">
                   {{-- <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Sign In</h2> --}}
                </div>
                <div class="card-body">

                    @include('_includes/errors')

                    {!! Form::open(['url' => '/password/reset', 'class' => '']) !!}

                        <input type="hidden" name="token" value="{{ $token }}">

                        {{ Form::hidden('token', $token) }}

                        <div class="form-group mb-3">
                            <label>E-Mail Address</label>
                            <div class="input-group">
                                {{ Form::text('email', $email ?? old('email'), ['class' => 'form-control form-control-lg', 'required' => true, 'autofocus' => true]) }}
                                <span class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>New Password</label>
                            <div class="input-group">
                                {{ Form::password('password', ['class' => 'form-control form-control-lg', 'required' => true]) }}
                                <span class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label>Confirm Password</label>
                            <div class="input-group">
                                {{ Form::password('password_confirmation', ['class' => 'form-control form-control-lg', 'required' => true]) }}
                                <span class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-8">

                            </div>
                            <div class="col-sm-4 text-right">
                                <button type="submit" class="btn btn-primary mt-2">Reset Password</button>
                            </div>
                        </div>

                    {!! Form::close() !!}

                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">&copy; Copyright {{ date("Y") }}. All Rights Reserved.</p>
        </div>
    </section>
    <!-- end: page -->

@endsection
