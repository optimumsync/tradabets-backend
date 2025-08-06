@extends('_layouts.master')

@section('main-title', 'Bank Accounts')

@section('main-content')

    {!! Form::open(['url' => '/add_account', 'method'=>'POST', 'class' => 'form-horizontal', 'files'=>true, 'onsubmit'=>'']) !!}

    <section class="card">
			<h3><b>Add Bank Account</b></h3>

        <div class="card-body">

            {{--     @include('_components/tabs/top', ['tab_link_arr' => $tab_link_arr, 'active' => 'Details'])    --}}

            <div class="row">
                <div class="col-md-12 col-lg-9">

                    {{ Form::text_md6('Account Name:', 'form[account_name]', old('form[account_name]'), ['required' => true, 'autofocus' => true]) }}

                    {{ Form::text_md6('Account Number:', 'form[account_number]', old('form[account_number]'), ['required' => true]) }}
                    <div class="form-group row">
                        {{ Form::label('bank', 'Bank:', ['class' => 'col-xs-4 col-md-4 control-label text-sm-right']) }}
                        <div class="col-xs-8 col-md-8">
                            {{ Form::select('bank', $bank_list, null, ['placeholder' => 'Select your Bank', 'required' => 'true', 'class' => 'form-control']) }}
                        </div>
                    </div>
                </div>

            </div> <!-- /row -->

            @include('_components.tabs.btm')

        </div><!-- /card-body -->

        <footer class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <span class="account-message">{{ @$error }}</span>
                </div>


                <div class="col-md-6 text-right">
                    {{ Form::submit('Save', ['class' => 'btn btn-primary']) }}
                </div>
            </div> <!-- /row -->
        </footer>

    </section>

    {!! Form::close() !!}

@endsection
