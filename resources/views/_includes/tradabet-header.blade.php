@php
    $avail_balance = Session::get('avail_balance');
@endphp

<script>
function myFunction() {
  var x = document.getElementById("myTopnav");
  if (x.className === "navbar") {
    x.className += " responsive";
  } else {
    x.className = "navbar";
  }
}

function myFunctionTogg() {
  var x = document.getElementById("logindiv");
  if (x.style.display === "block") {
    x.style.display = "none";
  } else {
    x.style.display = "block";
  }
}

</script>

    <section class="body">

         <div class="row home-page-header">

             <div class="container container-template">

                 <div class="row">

                    <div class="col-xl-4 col-md-3 col-xs-12 col-5 logo-container">
                        <a href="{{route('/')}}" class="logo">
                            <img src="/themes/admin/img/tradabets-logo.png" alt="Tradabet" />
                        </a>
                    </div>

                    @if(!auth::check())

                    <div class="col-7 sign-mob">
                        <a id="game-join-btn-id" class="btn btn-primary ml-1" data-toggle="modal" data-target="#registerModal">Join Now</a>
                        <a id="game-join-btn-id" class="btn btn-primary" onclick="myFunctionTogg()">Login</a>
                    </div>

                     <div class="col-xl-8 col-md-9 col-xs-12 col-centered pt-2 pb-1" id="logindiv">
                         {!! Form::open(['url' => '/login', 'class' => 'user-login-form', 'id'=>'user-login-form']) !!}
                             <div class="form-row align-items-center hdr-login">
                                 <div class="col-xl-5 col-lg-4 col-sm-4 col-xs-12 col-centered">
                                     <div class="input-group">
                                         {{ Form::text('email', old('user_name'), ['class' => 'form-control login-email', 'required' => true, 'autofocus' => true, 'tabindex' => 1, 'placeholder' => "E-Mail Address/ Phone Number"]) }}
                                         <span class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-user"></i>
                                            </span>
                                        </span>
                                     </div>
                                 </div>
                                 <div class="col-xl-4 col-lg-4 col-sm-4 col-xs-12 col-centered">
                                     <div class="input-group">
                                         {{ Form::password('password', ['class' => 'form-control login-password', 'required' => true,'onfocusout'=>'loginPasswordVerify()', 'onkeypress'=>'loginPasswordMessageClear()', 'tabindex' => 2, 'placeholder' => "Password"]) }}
                                         <span class="input-group-append">
                                            <span class="input-group-text">
                                                <i class="fas fa-eye-slash is-show-password-icon hover-icon-cursor-pointer"></i>
                                            </span>
                                        </span>
                                     </div>
                                 </div>
                                 <div class="col-xl-3 col-lg-4 col-sm-4 col-xs-12 col-centered">
                                     <button type="button" class="btn btn-primary user-verify">Login</button>
                                     <a id="game-join-btn-id" class="btn btn-primary game-login-button-class ml-1" data-toggle="modal" data-target="#registerModal">Register</a>
                                 </div>
                             </div>
                            <span id="email-message" class="pull-left"></span>
                            <span id="password" class="pull-left"></span>
                         {!! Form::close() !!}
                     </div>
                         <div class="col-12 frgt-pwd">
                             <a href="#" class="text-white" data-toggle="modal" onclick="forgotPasswordModalShow()">Forgot Password?</a>
                         </div>
                     @endif

                    @if(auth::check())
                    <div class="col-7 sign-mob">
                        <a href="#" class="balance-txt">Available Balance: <span class="amount-balance">&#8358;&nbsp;</span><label class="user-balance-label"></label></span></a>
                    </div>
                    @endif

                     <!-- <div class="col-lg-4 col-xs-12 col-centered pt-3 @if(auth::check()) pb-2 @endif">
                         <ul class="notifications mb-0">
                             <li>
                                 <a href="{{ 'faqs' }}" class="">
                                     FAQ's
                                 </a>
                             </li>
                             <li>
                                 <a href="{{ 'responsible-gambling' }}" class="">
                                     Responsible Gambling
                                 </a>
                             </li>
                             <li class="contact-us-list-item">
                                 <a href="{{ 'contactus' }}" class="">
                                     Contact Us
                                 </a>
                             </li>
                         </ul>
                     </div> -->

                 </div>
             </div>

            </div>

<!-- </div> -->
            <div class="col-lg-12 row game-list-header">
            <!-- <div class="container menu-container container-template">
                <div class="responsive-menu"><i class="fa fa-bars"></i></div>
                <div class="games-list-menu">

                 <ul class="gaming-menu">
                    <li class="sports-list {{ (request()->segment(1) == 'casino') ? 'active' : '' }}"><a href="{{ 'Casino' }}">Casino</a></li>
                    <li class="sports-list {{ (request()->segment(1) == 'bingo') ? 'active' : '' }}"><a href="{{ 'Bingo' }}">Bingo</a></li>
                    <li class="sports-list {{ (request()->segment(1) == 'sports') ? 'active' : '' }}"><a href="{{ 'sports' }}">Sports</a></li>
                    <li class="sports-list {{ (request()->segment(1) == 'bet') ? 'active' : '' }}"><a href="{{ 'bet' }}">Live Betting</a></li>
                    <li class="sports-list {{ (request()->segment(1) == 'virtualbetting') ? 'active' : '' }}"><a href="{{ 'Virtualbetting' }}">Virtual Betting</a></li>
                    <li class="sports-list {{ (request()->segment(1) == 'scheduledvirtual') ? 'active' : '' }}"><a href="{{ 'Scheduledvirtual' }}">Scheduled Virtual</a></li>
                    <li class="sports-list {{ (request()->segment(1) == 'jackpot') ? 'active' : '' }}"><a href="{{ 'Jackpot' }}">Jackpot</a></li>
                    <li class="sports-list {{ (request()->segment(1) == 'jackpot') ? 'active' : '' }}"><a href="#" data-toggle="dropdown" id="userbox">Results <i class="fa custom-caret"></i></a>
                        <ul class="dropdown-menu list-unstyled mb-2 my-account-dropdown">
                            <li class="profile-list">
                                <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="{{ route('home') }}" target="_blank">Bingo Result</a>
                            </li>
                            <li class="profile-list">
                                <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="{{ route('home') }}" target="_blank">Sport Result</a>
                            </li>
                        </ul>
                    </li>
{{--                <li class="sports-list {{ (request()->segment(1) == 'games') ? 'active' : '' }}"><a href="{{ 'Games' }}">Games</a></li>--}}
{{--                <li class="sports-list {{ (request()->segment(1) == 'poker') ? 'active' : '' }}"><a href="{{ 'Poker' }}">Poker</a></li>--}}
                     <li class="sports-list {{ (request()->segment(1) == 'promotions') ? 'active' : '' }} @if(!auth::check()) mr-0  @endif"><a href="{{ 'Promotions' }}">Promotions</a></li>
                     <li class="sports-list {{ (request()->segment(1) == 'jackpot') ? 'active' : '' }}"><a href="#" data-toggle="dropdown" id="userbox">Bet Slip <i class="fa custom-caret"></i></a>
                         <ul class="dropdown-menu list-unstyled mb-2 my-account-dropdown">
                             <li class="profile-list">
                                 <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="{{ route('home') }}" target="_blank">Check Bet</a>
                             </li>
                             <li class="profile-list">
                                 <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="{{ route('home') }}" target="_blank">Booking</a>
                             </li>
                         </ul>
                     </li>
                     <li class="sports-list {{ (request()->segment(1) == 'promotions') ? 'active' : '' }} @if(!auth::check()) mr-0  @endif"><a href="{{ 'Promotions' }}">Membership</a></li>
                     @if(auth::check())
                         <li class="sports-list myaccount-menu-item"><a href="#" data-toggle="dropdown" id="userbox">My Account <i class="fa custom-caret"></i></a>
                             <ul class="dropdown-menu list-unstyled mb-2 my-account-dropdown">
                                 <li>
                                     <input type="hidden"class="balance-amount-class" value="{{$avail_balance}}">
                                     <span class="dropdown-balance">Available Balance<br><span class="amount-balance">&#8358;&nbsp;</span><label class="user-balance-label"></label></span></li>
                                 <li class="divider dropdown-divider"></li>
                                 <li class="profile-list">
                                     <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="{{ route('home') }}" target="_blank"><i class="fas fa-user" style="padding-right:10px"></i> My Profile</a>
                                 </li>
                                 <li class="profile-list">
                                     <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/deposits" target="_blank"><i class="fas fa-money-bill" style="padding-right:10px"></i> Deposit</a>
                                 </li>
                                 <li class="profile-list">
                                     <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/withdraw" target="_blank"><i class="fas fa-paper-plane" style="padding-right:10px"></i> Withdraw</a>
                                 </li>
                                 <li class="profile-list">
                                     <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/bank-accounts" target="_blank"><i class="fas fa-university" style="padding-right:10px"></i> Bank accounts</a>
                                 </li>
                                 <li class="profile-list">
                                     <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/transaction" target="_blank"><i class="fas fa-list-alt" style="padding-right:10px"></i> My Transactions</a>
                                 </li>
                                 <li class="profile-list">
                                     <a class="dropdown-item dashboard-logout" href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                         <i class="fa fa-power-off" style="padding-right:13px"></i>{{ __('Logout') }}
                                     </a>

                                     <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                         @csrf
                                     </form>
                                 </li>
                             </ul>
                         </li>
                     @endif
                 </ul>
                </div>
            </div> -->


            <!-- new menu -->
            <div class="container menu-container container-template">
            <div class="navbar" id="myTopnav">
                <a class="{{ (request()->segment(1) == 'casino') ? 'active' : '' }}" href="{{ 'Casino' }}"><span>Casino</span></a>
                <!-- <a class="{{ (request()->segment(1) == 'bingo') ? 'active' : '' }}" href="{{ 'Bingo' }}"><span>Bingo</span></a> -->
                @auth
                    <a class="{{ (request()->segment(1) == 'sports') ? 'active' : '' }}" href="{{ 'sports' }}"><span>Sports</span></a>
                @endauth
                @guest
                    <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="Login to Bet" ><span>Sports</span></a>
                @endguest

                @auth
                    <a class="{{ (request()->segment(1) == 'bet') ? 'active' : '' }}" href="{{ 'bet' }}"><span>Sparket</span></a>
                @endauth
                @guest
                    <a href="javascript:void(0);" data-toggle="tooltip" data-placement="bottom" title="Login to Bet"><span>Sparket</span></a>
                @endguest

                    <a href="https://affiliate.tradabets.com" target="_blank"><span>Affiliate</span></a>
                <!-- <a class="{{ (request()->segment(1) == 'virtualbetting') ? 'active' : '' }}" href="{{ 'Virtualbetting' }}"><span>Virtual Betting</span></a>
                <a class="{{ (request()->segment(1) == 'scheduledvirtual') ? 'active' : '' }}" href="{{ 'Scheduledvirtual' }}"><span>Scheduled Virtual</span></a>
                <a class="{{ (request()->segment(1) == 'jackpot') ? 'active' : '' }}" href="{{ 'Jackpot' }}"><span>Jackpot</span></a>

                <div class="subnav">
                    <button class="subnavbtn {{ (request()->segment(1) == 'jackpot') ? 'active' : '' }}"><span>Results <i class="fa fa-chevron-down"></i></span></button>
                    <div class="subnav-content">
                        <div class="container">
                            <a href="{{ route('home') }}" target="_blank">Bingo Result</a>
                            <a href="{{ route('home') }}" target="_blank">Sport Result</a>
                        </div>
                    </div>
                </div> -->

                {{-- <a class="{{ (request()->segment(1) == 'games') ? 'active' : '' }}" href="{{ 'Games' }}"><span>Games</span></a>--}}
                {{-- <a class="{{ (request()->segment(1) == 'poker') ? 'active' : '' }}" href="{{ 'Poker' }}"><span>Poker</span></a>--}}
                <!-- <a class="{{ (request()->segment(1) == 'promotions') ? 'active' : '' }} @if(!auth::check())  @endif" href="{{ 'Promotions' }}"><span>Promotions</span></a>

                <div class="subnav">
                    <button class="subnavbtn {{ (request()->segment(1) == 'jackpot') ? 'active' : '' }}"><span>Bet Slip <i class="fa fa-chevron-down"></i></span></button>
                    <div class="subnav-content">
                        <div class="container">
                            <a href="{{ route('home') }}" target="_blank">Check Bet</a>
                            <a href="{{ route('home') }}" target="_blank">Booking</a>
                        </div>
                    </div>
                </div>

                <a href="{{ 'Promotions' }}" class="{{ (request()->segment(1) == 'promotions') ? 'active' : '' }} @if(!auth::check()) @endif"><span>Membership</span></a> -->

                @if(auth::check())

                <div class="subnav">
                    <button class="subnavbtn {{ (request()->segment(1) == 'jackpot') ? 'active' : '' }}"><span>My Account <i class="fa fa-chevron-down"></i></span></button>
                    <div class="subnav-content">
                        <div class="container">
                            <input type="hidden"class="balance-amount-class" value="{{$avail_balance}}">
                            <a href="#">Available Balance: <span class="amount-balance">&#8358;&nbsp;</span><label class="user-balance-label"></label></span></a>
                            <a href="{{ route('home') }}" target="_blank"><i class="fas fa-user"></i> My Profile</a>
                            <a href="/deposits" target="_blank"><i class="fas fa-money-bill"></i> Deposit</a>
                            <a href="/withdraw" target="_blank"><i class="fas fa-paper-plane"></i> Withdraw</a>
                            <a href="/bank-accounts" target="_blank"><i class="fas fa-university"></i> Bank accounts</a>
                            <a href="/transaction" target="_blank"><i class="fas fa-list-alt"></i> My Transactions</a>
                            <a href="{{ route('logout') }}"
                                        onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                         <i class="fa fa-power-off"></i>{{ __('Logout') }}
                                     </a>

                            <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                @csrf
                            </form>
                        </div>
                    </div>
                </div>
                @endif
                <a href="javascript:void(0);" class="icon" onclick="myFunction()">&#9776;</a>
            </div>
            </div>
            <!-- new menu -->

        </div>


        <!--login Modal-->
        <div id="loginModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><div class="card-title-sign mt-3 text-right">
                                <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Sign In</h2>
                            </div></h4>
                    </div>
                    <div class="modal-body">
                        <div class="card-body">

                            @if (session('status'))
                                <div class="alert alert-success" role="alert">
                                    {{ session('status') }}
                                </div>
                            @endif

                            {!! Form::open(['url' => '/login', 'class' => 'user-login-form']) !!}


                            <div class="form-group mb-3">
                                <label>E-Mail Address/ Phone Number</label>
                                <div class="input-group">
                                    {{ Form::text('email', old('user_name'), ['class' => 'form-control form-control-lg login-email', 'required' => true, 'autofocus' => true, 'tabindex' => 1]) }}
                                    <span class="input-group-append">
                                        <span class="input-group-text">
                                            <i class="fas fa-user"></i>
                                        </span>
                                    </span>
                                </div>
                                <span id="email-message"></span>
                            </div>

                            <!-- <div class="form-group mb-3">
                                <label>E-Mail Address</label>
                                <div class="input-group">
                                    {{ Form::text('email', old('user_name'), ['class' => 'form-control form-control-lg login-email', 'required' => true, 'autofocus' => true, 'onfocusout'=>'loginEmailVerify()', 'tabindex' => 1]) }}
                                    <span class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </span>
                                </div>
                                <span id="email-message"></span>
                            </div> -->

                            <div class="form-group mb-3">
                                <div class="clearfix">
                                    <label class="float-left">Password</label>

                                </div>
                                <div class="input-group">
                                    {{ Form::password('password', ['class' => 'form-control form-control-lg login-password', 'required' => true,'onfocusout'=>'loginPasswordVerify()', 'onkeypress'=>'loginPasswordMessageClear()', 'tabindex' => 2]) }}
                                    <span class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-eye-slash is-show-password-icon hover-icon-cursor-pointer"></i>
                                    </span>
                                </span>
                                </div>
                                <span id="password"></span>
                            </div>

                            <div class="row">

                                <div class="col-sm-12 ">
                                    <button type="button" class="btn btn-primary mt-2" onclick="userVerify()">Login</button>
                                        <a href="#" class="float-right" data-toggle="modal" onclick="forgotPasswordModalShow()">Forgot Password?</a>
                                </div>
                                {{-- <a href="{{ url('auth/google') }}" class="btn btn-lg btn-primary btn-block">
                                    <strong>Login With Google</strong>
                                </a> --}}
                            </div>

                           {{-- <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                            <span>or</span>
                           </span> --}}

                            {{-- <p class="text-center">Don't have an account yet? <a href="#" data-toggle="modal" data-target="#registerModal" onclick="$('#loginModal').modal('hide')">Sign Up!</a></p> --}}

                            {!! Form::close() !!}

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--login OTP Modal-->
        <div id="loginOtpModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><div class="card-title-sign mt-3 text-right">
                                <h2 class="title text-uppercase font-weight-bold m-0">OTP</h2>
                            </div>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="card-body">

                            <div id="emailMobileOtpError"></div>
                            <div class="form-group mb-3">
                                <label>E-Mail OR Phone OTP</label>
                                <div class="input-group">
                                    {{ Form::text('email_mobile_otp', old('email_mobile_otp'), ['class' => 'form-control email_mobile_otp', 'required' => true]) }}
                                </div>
                                <span id="email-message"></span>
                            </div>

                            <div class="row">

                                <div class="col-sm-12 ">
                                    <button type="button" class="btn btn-primary mt-2" id="otp_verify">Verify</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Forgot Password OTP Modal-->
        <div id="passwordOtpModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><div class="card-title-sign mt-3 text-right">
                                <h2 class="title text-uppercase font-weight-bold m-0">OTP</h2>
                            </div>
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="card-body">
                            <div id="emailMobileOtpError"></div>
                            <div class="form-group mb-3">
                                <label>E-Mail OTP</label>
                                <div class="input-group">
                                    {{ Form::text('femail_otp', old('femail_otp'), ['class' => 'form-control forgot-email-otp', 'required' => true]) }}
                                </div>
                                <span id="reset-message"></span>
                            </div>

                            <div class="row">
                                <div class="col-sm-12 ">
                                    <button type="button" class="btn btn-primary mt-2" onclick="forgotPasswordOtpVerify()">Verify</button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--Register Modal -->
        <div id="registerModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title"><div class="card-title-sign mt-3 text-right">
                                <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i>Register</h2>
                            </div></h4>
                    </div>
                    <div class="modal-body">
                        <div class="card-body">

                        {!! Form::open(['url' => '/register', 'class' => '','name'=>'registration','id'=>'registration_form']) !!}

                        <fieldset id="first-page">
                            <div class="form-group mb-3">
                                <label>First Name</label>
                                {{ Form::text('first_name', old('first_name'), ['class' => 'form-control first-name', 'required' => true,'onkeypress'=>'return onlyCharacters(event,this)']) }}
                            </div>

                            <div class="form-group mb-3">
                                <label>Last Name</label>
                                {{ Form::text('last_name', old('last_name'), ['class' => 'form-control last-name', 'required' => true, 'onkeypress'=>'return onlyCharacters(event,this)']) }}
                            </div>

                            <div class="form-group mb-3">
                                <label>Date of Birth</label>
                                {{--<input class="form-control" name="form[date_of_birth]" type="text" id="datepicker"><span><i class="fas fa-calendar-alt"></i></span>--}}
                                <div class="input-group"><input class="form-control date-of-birth" name="date_of_birth" type="text" value="" id="datepicker" required autocomplete="false"><span class="input-group-append"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span></span></div>
                            </div>

                            <div class="form-group mb-3">
                                <label>E-Mail Address</label>
                                {{ Form::text('email', old('email'), ['class' => 'form-control email-input', 'required' => true, 'onfocusout' =>'emailVerify()', 'onkeypress'=>'emailMessageClear()']) }}
                                <span id="email-id"></span>
                            </div>

                            <div class="form-group mb-0">
                                <div class="row">
                                    <div class="col-sm-6 mb-3">
                                        <label>Password</label>
                                        {{ Form::password('password', ['class' => 'form-control password', 'required' => true, 'id'=>'password','onfocusout' =>'passwordVerify()', 'onkeypress'=>'passwordMessageClear()']) }}
                                        <span id="password-message"></span>
                                    </div>
                                    <div class="col-sm-6 mb-3">
                                        <label>Password Confirmation</label>
                                        {{ Form::password('password_confirmation', ['class' => 'form-control confirm-password', 'required' => true,'onfocusout' =>'confirmPasswordVerify()', 'onkeypress'=>'confirmPasswordMessageClear()']) }}
                                        <span id="pwd-message"></span>
                                    </div>

                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-8">
                                    <span class="mandatory-fields"></span>
                                </div>
                                <div class="col-sm-4 text-right">
                                    <a type="button"  class="next action-button btn btn-primary mt-2 text-white" onclick="registerToggle()">Next</a>
                                </div>
                            </div>
                        </fieldset>

                        <fieldset id="second-page">

                           {{-- <div class="form-group mb-0">
                                <div class="row">
                                    <div class="col-sm-10 mb-3">
                                        <label>Country</label>
                                        {{ Form::text('country','Nigeria', ['class' => 'form-control readonly', 'required' => true]) }}<span></span>
                                    </div>
                                  --}}
                                  {{--  <div class="col-sm-2 mb-3">
                                        <button type="button" class="btn btn-primary" data-toggle="modal" id="province_fetchID" title="Click here to select the province."><i class="fa fa-map-pin"></i></button>
                                    </div>--}}
                                    {{--
                                </div>
                            </div>--}}

                            <div class="form-group mb-0">
                                <div class="row">
                                    <div class="col-sm-4 mb-3">
                                        <label>Country Code</label>
                                        @include('_includes.country-code')
                                    </div>
                                    <div class="col-sm-8 mb-3">
                                        <label>Phone</label>
                                        {{ Form::text('phone', old('phone'), ['class' => 'form-control phone', 'required' => true,'maxlength'=>12, 'onkeypress'=>'return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13) ? null : event.charCode >= 48 && event.charCode <= 57','onfocusout' =>'phoneNumberVerify()', 'onkeydown'=>'phoneMessageClear()']) }}

                                        <!-- {{ Form::text('phone', old('phone'), ['class' => 'form-control phone', 'required' => true,'maxlength'=>12, 'onfocusout' =>'phoneNumberVerify()', 'onkeypress'=>'phoneMessageClear()']) }} -->
{{--                                        {{ Form::text('phone', old('phone'), ['class' => 'form-control phone', 'required' => true, 'onfocusout' => 'phoneNumberVerify()', 'onkeydown'=>'phoneMessageClear()']) }}--}}
                                        <span id="phone"></span>
                                    </div>

                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label>Country</label>
                                {{ Form::text('country','Nigeria', ['class' => 'form-control readonly', 'required' => true]) }}
                            </div>

                            <div class="form-group mb-3">
                                <label>State</label>
                                {{ Form::text('state', old('country'), ['class' => 'form-control', 'required' => true,'onkeypress'=>'return onlyCharacters(event,this)']) }}
                            </div>

                            <div class="form-group mb-3">
                                <label>City</label>
                                {{ Form::text('city', old('city'), ['class' => 'form-control', 'required' => true,'onkeypress'=>'return onlyCharacters(event,this)']) }}
                            </div>

                            <div class="form-group mb-3">
                                <label>Promo Code</label>
                                {{ Form::text('promo_code', old('promo_code'), ['class' => 'form-control']) }}
                            </div>

                            <div class="row">
                                <div class="col-sm-4">
                                    <a class="btn btn-primary mt-2 text-white" onclick="registerToggle()">Previous</a>
                                </div>
                                <div class="col-sm-4">

                                </div>
                                <div class="col-sm-4 text-right">
                                    <a type="button"  class="next action-button btn btn-primary mt-2 text-white" onclick="registerToggleThree()">Next</a>
                                </div>
                            </div>
                        </fieldset>

                            <fieldset id="third-page">
                                <div id="phoneOtpError"></div>
                                <div class="form-group mb-3">
                                    <label>Mobile OTP</label>
                                    {{ Form::text('mobile_otp', old('first_name'), ['class' => 'form-control mobile_otp', 'required' => true]) }}
                                </div>

                                <div id="emailOtpError"></div>
                                <!-- <div class="form-group mb-3">
                                    <label>Email OTP</label>
                                    {{ Form::text('email_otp', old('last_name'), ['class' => 'form-control email_otp', 'required' => true]) }}
                                </div> -->

                                <div class="row">
                                    <div class="col-sm-4">
                                        <a class="btn btn-primary mt-2 text-white" onclick="registerToggleThree()">Previous</a>
                                    </div>
                                    <div class="col-sm-4">
                                    </div>
                                    <div class="col-sm-4 text-right">
                                        <button type="button" class="btn btn-primary mt-2 register-user">Register</button>
                                    </div>
                                </div>
                            </fieldset>

                        {{-- <span class="mt-3 mb-3 line-thru text-center text-uppercase">
                            <span>or</span>
                        </span>

                           <p class="text-center">Already have an account? <a href="#" data-toggle="modal" data-target="#loginModal" onclick="$('#registerModal').modal('hide')">Login!</a></p> --}}

                            {!! Form::close() !!}

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!--lost-password-modal -->
        <div id="forgotPasswordModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">    <div class="card-title-sign mt-3 text-right">
                                <h2 class="title text-uppercase font-weight-bold m-0"><i class="fas fa-user mr-1"></i> Recover Password</h2>
                            </div>
                    </div>
                    <div class="modal-body">
                        <div class="card-body">
                            <div class="alert alert-info">
                                <p class="m-0">Enter your e-mail below and we will send you reset instructions!</p>
                            </div>
{{--                            <span id="reset-message"></span>--}}

                            {!! Form::open(['url' => route('password.email'), 'class' => 'reset-password-form']) !!}

                            <div class="form-group mb-0">
                                <div class="input-group">
                                    {{ Form::text('email', old('email'), ['class' => 'form-control form-control-lg forgot-password-email', 'required' => true, 'autofocus' => true]) }}
                                    <span class="input-group-append">
                                        <button class="btn btn-primary btn-lg" type="button" onclick="forgotPasswordURL()">Reset!</button>
                                    </span>
                                </div>
                            </div>

                          {{--  <p class="text-center mt-3">Remembered? <a href="#" data-toggle="modal" data-target="#loginModal">Log In!</a></p>--}}

                            {!! Form::close() !!}

                        </div>
                    </div>
                </div>
            </div>
        </div>

    <!-- start: search & user box -->
    <!-- end: search & user box -->
    <!-- </header> -->

    <!-- end: header -->
