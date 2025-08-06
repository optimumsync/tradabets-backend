@extends('_layouts.master')

@section('main-title', 'KYC')

@section('main-content')
</br>
<div id="message-area">
    @if (session('error'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('error') }}
        </div>     
    @endif
</div>
    {!! Form::open(['url' => '/kyc-upload', 'method'=>'POST', 'class' => 'form-horizontal', 'files'=>true]) !!}


    <section class="card">

        <div class="card-body">


            {{--   @include('_components/tabs/top', ['tab_link_arr' => $tab_link_arr, 'active' => 'Details'])  --}}

            <div class="row">
                <div class="col-md-12 col-lg-6">
                    {{--{{ Form::select('form[document_type]', config('custom-form.kyc-type-arr'), old('document_type'),['class' => 'form-control','required' => true]) }}--}}
                    {{ Form::text_md6('Name:', 'form[name]', old('form[name]'), ['required' => true, 'autofocus' => true]) }}

                    {{ Form::select_md6('ID Type:', 'form[document_type]',config('custom-form.kyc-type-arr'), old('document_type'), ['required' => true])}}

                    {{ Form::text_md6('ID Number:', 'form[id_number]', old('form[id_number]'), ['required' => true]) }}

                    {{ Form::file_md6('Upload:', 'content_file', [], null, ['required' => true]) }}
                    <div class="text-right">
                        <p style="color: red;">[Max file size: (JPEG: 100kb, PNG: 500kb)]</p>
                    </div>

                </div>


                        {{-- <div class="col-md-12 col-lg-6">

                     <h4>Profile Image</h4>

                         <hr />



                     <div class="row">
                         <div class="col-md-6 offset-md-3 col-lg-4 offset-lg-4">

                             @if($user->profile_image_thumbnail_path)
                                 <img src="{{ asset($user->profile_image_thumbnail_path) }}" class="img-thumbnail rounded">
                             @endif

                         </div>
                     </div> <!-- /row -->

                 </div>--}}
            </div> <!-- /row -->

            @include('_components.tabs.btm')

        </div>
        <footer class="card-footer">

            <div class="row">
                <div class="col-md-6 text-right">
                    {{ Form::submit('Upload', ['class' => 'btn btn-primary']) }}
                </div>
            </div> <!-- /row -->

        </footer>
    </section>

    {!! Form::close() !!}

@endsection
