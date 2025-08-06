
@php
    $avail_balance = Session::get('avail_balance');

@endphp
    <section class="body">

        <!-- start: header -->
        <header class="header">
            <div class="admin-logo-container">
                <a href="/home" class="logo">
                    <img src="/themes/admin/img/logo-placeholder.png" style="width: 185px;" alt="Porto Admin" />
                </a>

                <div class="d-md-none toggle-sidebar-left"  data-target="html">
                    <i class="fas fa-bars"  aria-label="Toggle sidebar"></i></div>
            </div>

            <div class="left-menu">
                @if(auth()->user()->role!='admin')

                    <ul class="nav nav-main">
                        <li>
                            <a href="/betlist" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/betlist.png"><span>Bet List</span></a>
                        </li>
                        <li>
                            <a href="/betlist-cashout" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/betlistcashout.png"><span>Bet List Cashout</span></a>
                        </li>
                        <li>
                            <a href="/transaction" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/transactionlist.png"><span>Transaction List</span></a>
                        </li>
                        <li>
                            <a href="/active-bonus" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/activebbonus.png"><span>Active Bonus</span></a>
                        </li>
                        <li>
                            <a href="/rewards" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/rewards.png"><span>Rewards</span></a>
                        </li>
                        <li>
                            <a href="/bonus-transaction-list" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/bonustransactionlist.png"><span>Bonus Transaction List</span></a>
                        </li>
                        <li>
                            <a href="/inbox" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/messages.png"><span>Messages</span><span class="float-right badge badge-primary">1</span></a>
                        </li>
                        <li>
                            <a href="/deposits" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/deposits.png"><span>Deposit</span></a>
                        </li>
                        <li>
                            <a href="/withdraw" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/withdraw.png"><span>WithDraw</span></a>
                        </li>
                        <li>
                            <a href="/document-upload" class="nav-link"><img class="user-menu-icons" src="/themes/admin/img/kycinfo.png"><span>KYC</span></a>
                        </li>

                    </ul>

                @else
                    <ul class="nav nav-main">
                        <li>
                            <a href="/transaction-view" class="nav-link"><i class="fas fa-file" aria-hidden="true"></i><span>Users-Transaction-list</span></a>
                        </li>
                        <li>
                            <a href="/balance-view" class="nav-link"><i class="fas fa-file" aria-hidden="true"></i><span>Users-Balance-list</span></a>
                        </li>
                        <li>
                            <a href="/withdraw-requests" class="nav-link"><i class="fas fa-file" aria-hidden="true"></i><span>Users-Withdraw-Requests</span></a>
                        </li>
                        <li>
                            <a href="/kyc-list" class="nav-link"><i class="fas fa-file" aria-hidden="true"></i><span>Users-KYC-list</span></a>
                        </li>
                        <li>
                            <a href="/inbox" class="nav-link"><i class="fas fa-envelope" aria-hidden="true"></i><span>Messages</span><span class="float-right badge badge-primary">1</span></a>
                        </li>
                    </ul>
                @endif
            </div>

            <!-- start: search & user box -->
            <div class="header-right">

                @include('_components/pages/header-notifications')

                <span class="separator"></span>

                <div id="userbox" class="userbox">
                    <a href="#" data-toggle="dropdown" class="my-account-dropdown">
                        <figure class="profile-picture">
                            @if(auth()->user()->profile_image_thumbnail_path)
                                <img src="{{ asset(auth()->user()->profile_image_thumbnail_path) }}" class="rounded-circle">
                            @else
                                <img src="/themes/admin/img/!logged-user.jpg" class="rounded-circle" data-lock-picture="/themes/admin/img/!logged-user.jpg" />
                            @endif
                        </figure>
                        <div class="profile-info" data-lock-name="{{ auth()->user()->full_name  }}" data-lock-email="johndoe@okler.com">
                            <span class="name">{{ auth()->user()->first_name }}</span>
                            @if(auth()->user()->role!='admin')
                            <input type="hidden"class="balance-amount-class" value="{{$avail_balance}}">
                            <span class="role">	&#8358;&nbsp;<label class="dashboard-amount-balance"></label></span>
                                @endif
                        </div>

                        <i class="fa custom-caret"></i>
                    </a>

                    <div class="dropdown-menu my-account-dropdown">
                        <ul class="list-unstyled mb-2">
                            <li class="divider"></li>
                            <li>
                                <a role="menuitem" tabindex="-1" href="/users/profile/{{ auth()->user()->id }}"><i class="fas fa-user"></i> My Profile</a>
                            </li>
                            @if(auth()->user()->role!='admin')
                            <li>
                                <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/deposits"><i class="fas fa-money-bill"></i> Deposit</a>
                            </li>
                            <li>
                                <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/withdraw"><i class="fas fa-paper-plane"></i> Withdraw</a>
                            </li>
                            <li>
                                <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/bank-accounts"><i class="fas fa-university"></i> Bank accounts</a>
                            </li>
                            <li>
                                <a class="dropdowm-item dashboard-anchor" tabindex="-1" href="/transaction"><i class="fas fa-list-alt"></i> My Transactions</a>
                            </li>
                            @endif
                            <li>
                               <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                   <i class="fa fa-power-off"></i> {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- end: search & user box -->
        </header>
        <!-- end: header -->

        <div class="inner-wrapper">

            <!-- start: sidebar -->
            <aside id="sidebar-left" class="sidebar-left">

                <div class="sidebar-header">
                    <div class="sidebar-title">
                        Navigation
                    </div>
                    <div class="sidebar-toggle d-none d-md-block" data-toggle-class="sidebar-left-collapsed" data-target="html" data-fire-event="sidebar-left-toggle">
                        <i class="fas fa-bars" aria-label="Toggle sidebar"></i>
                    </div>
                </div>

                <div class="nano">
                    <div class="nano-content">
                        <nav id="menu" class="nav-main" role="navigation">


                                @if(auth()->user()->role!='admin')

                            {!! MenuHelper::create_menu( config('custom-menus.main-menu'), config('custom-menus.main-menu-settings') ) !!}

                            @else
                                {!! MenuHelper::create_menu( config('custom-menus.admin-menu'), config('custom-menus.main-menu-settings') ) !!}
                            @endif
                        </nav>

                    </div>

                    <script>
                        // Maintain Scroll Position
                        if (typeof localStorage !== 'undefined') {
                            if (localStorage.getItem('sidebar-left-position') !== null) {
                                var initialPosition = localStorage.getItem('sidebar-left-position'),
                                    sidebarLeft = document.querySelector('#sidebar-left .nano-content');

                                sidebarLeft.scrollTop = initialPosition;
                            }
                        }
                    </script>


                </div>

            </aside>
            <!-- end: sidebar -->

            <section role="main" class="content-body card-margin">

                <header class="page-header">
                    <h2>@yield('main-title')</h2>
                </header>

                <!-- start: page -->
                <div class="row">
                    <div class="col-md-12">

                        {{-- TODO: refactor --}}

                        @if(session('message-success'))

                                <p>&nbsp;</p>

                            <div class="alert alert-success" role="alert">
                                {{ session('message-success') }}
                            </div>

                        @endif

                        @if(session('message-info'))

                                <p>&nbsp;</p>

                            <div class="alert alert-info" role="alert">
                                {{ session('message-info') }}
                            </div>

                        @endif

                        @if(session('message-warning'))

                                <p>&nbsp;</p>

                            <div class="alert alert-warning" role="alert">
                                {{ session('message-warning') }}
                            </div>

                        @endif
