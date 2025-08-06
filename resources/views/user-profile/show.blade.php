@extends('_layouts.master')

@section('main-title', 'User Profile')

@section('main-content')

    <section class="card">
        <header class="card-header">

            @include('_components/pages/card-title', [
                    'title' => 'View',
                    'description' => $user->first_name,
                ])

        </header>
        
        <div class="card-body">

            @php
                $tab_link_arr = [
                        '/users/profile/'.$user->id => 'Details',
                    ];
            @endphp

            {{--@include('_components/tabs/top', ['tab_link_arr' => $tab_link_arr, 'active' => 'Details'])--}}

                <div class="row">
                    <div class="col-md-6 col-lg-6">

                        @include('_components.form-elements.text-view-md6', ['label' => 'First Name', 'value' => $user->first_name])

                        @include('_components.form-elements.text-view-md6', ['label' => 'Last Name', 'value' => $user->last_name])

                        @include('_components.form-elements.text-view-md6', ['label' => 'E-Mail Address', 'value' => $user->email])

                        @include('_components.form-elements.text-view-md6', ['label' => 'Phone', 'value' => $user->phone])

                        @include('_components.form-elements.text-view-md6', ['label' => 'City', 'value' => $user->city])

                        @include('_components.form-elements.text-view-md6', ['label' => 'State', 'value' => $user->state])

                        @include('_components.form-elements.text-view-md6', ['label' => 'Country', 'value' => $user->country])



                    </div>
                 {{--   <div class="col-md-6 col-lg-6 text-right">

                        <div class="row">
                            <div class="col-md-12 col-lg-6 offset-lg-6">

                                @if($user->profile_image_thumbnail_path)
                                    <img src="{{ asset($user->profile_image_thumbnail_path) }}" class="img-thumbnail rounded">
                                @endif

                            </div>
                        </div> <!-- /row -->

                    </div>--}}
                </div> <!-- /row -->

            @include('_components/tabs/btm')

        </div>
        <footer class="card-footer">

             <div class="row">
                 <div class="col-md-6">

                 </div>
                 <div class="col-md-6 text-right">
                     <a href="/users/profile/{{ $user->id }}/edit" class="btn btn-primary">Edit Profile</a>
                 </div>
             </div> <!-- /row -->

        </footer>
    </section>

@endsection
