@extends('_layouts.master')

@section('main-title', 'Security Alert')

@section('main-content')

    <!-- start: page -->
    <section class="body-error error-inside">
        <div class="center-error">

            <div class="row">
                <div class="col-lg-8">
                    <div class="main-error mb-3">
                        <h2 class="error-code text-dark text-center font-weight-semibold m-0">Oops! <i class="fas fa-hand-paper"></i></h2>
                        <p class="error-explanation text-center">We're sorry, but you don't have permission to perform this action.</p>
                    </div>
                </div>
                <div class="col-lg-4">
                    <h4 class="text">Here are some useful links</h4>
                    <ul class="nav nav-list flex-column primary">
                        <li class="nav-item">
                            <a class="nav-link" href="/dashboard"><i class="fas fa-caret-right text-dark"></i> Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/users/profile/{{ auth()->user()->hash_id }}"><i class="fas fa-caret-right text-dark"></i> User Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/logout"><i class="fas fa-caret-right text-dark"></i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
    <!-- end: page -->

@endsection
