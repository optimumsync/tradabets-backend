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
                    <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Recover Password</h2>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <p class="m-0">Enter your e-mail below and we will send you reset instructions!</p>
                    </div>

                    @include('_includes/errors')

                    {!! Form::open(['url' => route('password.email'), 'class' => '']) !!}

                        <div class="form-group mb-0">
                            <div class="input-group">
                                {{ Form::text('email', old('email'), ['class' => 'form-control form-control-lg', 'required' => true, 'autofocus' => true]) }}
                                <span class="input-group-append">
                                    <button class="btn btn-primary btn-lg" type="submit">Reset!</button>
                                </span>
                            </div>
                        </div>

                        <p class="text-center mt-3">Remembered? <a href="/login">Log In!</a></p>

                    {!! Form::close() !!}

                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">&copy; Copyright {{ date("Y") }}. All Rights Reserved.</p>
        </div>
    </section>
    <!-- end: page -->
    <div id="forgotPasswordModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <a href="/" class="logo float-left">
                        <img src="/themes/admin/img/logo-placeholder.png" height="54" alt="{{ env('APP_NAME') }}" />
                    </a>
                    <h4 class="modal-title">    <div class="card-title-sign mt-3 text-right">
                            <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Recover Password</h2>
                        </div>
                </div>
                <div class="modal-body">
                    <div class="card-body">
                        <div class="alert alert-info">
                            <p class="m-0">Enter your e-mail below and we will send you reset instructions!</p>
                        </div>

                        @include('_includes/errors')

                        {!! Form::open(['url' => route('password.email'), 'class' => '']) !!}

                        <div class="form-group mb-0">
                            <div class="input-group">
                                {{ Form::text('email', old('email'), ['class' => 'form-control form-control-lg', 'required' => true, 'autofocus' => true]) }}
                                <span class="input-group-append">
                                    <button class="btn btn-primary btn-lg" type="submit">Reset!</button>
                                </span>
                            </div>
                        </div>

                        <p class="text-center mt-3">Remembered? <a href="/login">Log In!</a></p>

                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
