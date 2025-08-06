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
                    <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Register</h2>
                </div>
                <div class="card-body">

                    @include('_includes/errors')

                    {!! Form::open(['url' => '/register', 'class' => '']) !!}

                        

                        <div class="form-group mb-3">
                            <label>Name</label>
                            {{ Form::text('name', old('first_name'), ['class' => 'form-control', 'required' => true]) }}
                        </div>

                       
                        <div class="form-group mb-3">
                            <label>E-Mail Address</label>
                            {{ Form::text('email', old('email'), ['class' => 'form-control', 'required' => true]) }}
                        </div>

                         <div class="form-group mb-3">
                            <label>Phone</label>
                            <select name="country_code" style="width: 150px;">
                                            <option value="+1">(+1) US</option>
                                            <option value="+212">(+212) Morocco</option>
                                             <option value="+91">(+91) India</option>
                                        </select>
                        </div>

                         <div class="form-group mb-3">
                            <input id="phone" type="text" class="form-control" name="phone" required>

                                    @if ($errors->has('country_code'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('country_code') }}</strong>
                                    </span>
                                    @endif
                                    @if ($errors->has('phone'))
                                        <span class="help-block">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                                    @endif
                        </div>

                        <div class="form-group mb-0">
                            <div class="row">
                                <div class="col-sm-6 mb-3">
                                    <label>Password</label>
                                    {{ Form::password('password', ['class' => 'form-control', 'required' => true]) }}
                                </div>
                                <div class="col-sm-6 mb-3">
                                    <label>Password Confirmation</label>
                                    {{ Form::password('password_confirmation', ['class' => 'form-control', 'required' => true]) }}
                                </div>
                            </div>
                        </div>



                        <div class="row">
                            <div class="col-sm-8">

                            </div>
                            <div class="col-sm-4 text-right">
                                <button type="submit" class="btn btn-primary mt-2">Register</button>
                            </div>
                        </div>

                        <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                            <span>or</span>
                        </span>

                        <p class="text-center">Already have an account? <a href="/login">Login!</a></p>

                    {!! Form::close() !!}

                </div>
            </div>

            <p class="text-center text-muted mt-3 mb-3">&copy; Copyright {{ date("Y") }}. All Rights Reserved.</p>
        </div>
    </section>
    <!-- end: page -->

@endsection
