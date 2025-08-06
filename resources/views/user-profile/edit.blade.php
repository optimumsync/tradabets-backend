@extends('_layouts.master')

@section('main-title', 'User Profile')

@section('main-content')

    {!! Form::open(['url' => '/users/profile/'.$user->id, 'class' => 'form-horizontal']) !!}

    @method('PATCH')

    <section class="card">
        <header class="card-header">

            @include('_components/pages/card-title', [
                    'title' => 'Edit',
                    'description' => $user->first_name,
                ])

        </header>
        <div class="card-body">

            @php
                $tab_link_arr = [
                        '/users/profile/'.$user->id => 'Details',
                    ];
            @endphp

{{--            @include('_components/tabs/top', ['tab_link_arr' => $tab_link_arr, 'active' => 'Details'])--}}

                <div class="row">
                    <div class="col-md-12 col-lg-6">

                        {{ Form::text_md6('First Name:', 'form[first_name]', $user->first_name, ['required' => true, 'autofocus' => true,'onkeypress'=>'return onlyCharacters(event,this)']) }}

                        {{ Form::text_md6('Last Name:', 'form[last_name]', $user->last_name, ['required' => true,'onkeypress'=>'return onlyCharacters(event,this)']) }}

                        {{ Form::text_md6('E-Mail Address:', 'form[email]', $user->email, ['required' => true]) }}

                        {{ Form::text_md6('Phone:', 'form[phone]', $user->phone, ['required' => true,'maxlength'=>10,'onkeypress'=>'return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13) ? null : event.charCode >= 48 && event.charCode <= 57']) }}

                        {{ Form::text_md6('City:', 'form[city]', $user->city, ['required' => true]) }}

                        {{ Form::text_md6('State:', 'form[state]', $user->state, ['required' => true]) }}


                        <h4>Update Password:</h4>

                            <hr/>

                        {{ Form::password_md6('Password:', 'password') }}

                        {{ Form::password_md6('Password Confirm:', 'password_confirmation', [], '<small>If left empty the password will remain unchanged.</small>') }}

                    </div>
                   {{-- <div class="col-md-12 col-lg-6">

                        <h4>Profile Image</h4>

                            <hr />

                        {{ Form::file_md6('File:', 'content_file', [], null) }}

                        <div class="row">
                            <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">

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

             @include('_components/forms/edit-footer', ['cancel_url' => '/users/profile/'.$user->id, 'update' => true ])

        </footer>
    </section>

    {!! Form::close() !!}

@endsection
