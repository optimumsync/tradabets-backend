@extends('_layouts.master')

@section('main-title', 'Withdraw Requests')

@section('main-content')
    {!! Form::open(['url' => '/withdraw-request/update/'.$withdraw->id, 'class' => 'form-horizontal kyc-form', 'method'=>'GET']) !!}
    <section class="card">
        <header class="card-header">

            @include('_components/pages/card-title', [
                    'title' => 'View',
                    'description' =>'',
                ])

        </header>
        
        <div class="card-body">

            {{--@include('_components/tabs/top', ['tab_link_arr' => $tab_link_arr, 'active' => 'Details'])--}}

            <div class="row">
                <div class="col-md-6 col-lg-6">

                    @include('_components.form-elements.text-view-md6', ['label' => 'First Name', 'value' => $withdraw->user->first_name.' '. $withdraw->user->last_name])

                    @include('_components.form-elements.text-view-md6', ['label' => 'Amount', 'value' => $withdraw->amount])

                    @include('_components.form-elements.text-view-md6', ['label' => 'Requested Time', 'value' => $withdraw->created_at])

                    @include('_components.form-elements.text-view-md6', ['label' => 'Status', 'value' => $withdraw->status])
                    {{Form::hidden('form[status]',0,array("class"=>'status-message'))}}
                    {{-- @include('_components.form-elements.text-view-md6', ['label' => 'City', 'value' => $user->city])

                  @include('_components.form-elements.text-view-md6', ['label' => 'State', 'value' => $user->state])

                  @include('_components.form-elements.text-view-md6', ['label' => 'Country', 'value' => $user->country])--}}
                    <div class="row form-view-row">
                        <div class="col-xs-4 col-md-4 fnt-b">Remarks:</div>
                        <div class="col-xs-8 col-md-8"><input class="form-control" name="form[remarks]" type="text" value="" id="form[remarks]"></div>
                    </div>

                </div>

            </div> <!-- /row -->

            @include('_components/tabs/btm')

        </div>
        <footer class="card-footer">
            <div class="row">
                <div class="col-md-6">
                    <a href="/withdraw-requests" class="btn btn-default">Back</a>
                </div>
                <div class="col-md-6 text-right">
                    <input class="btn btn-primary" type="button" value="Approve" onclick="documentStatus(this)">
                    <input class="btn btn-danger" type="button" value="Reject" onclick="documentStatus(this)">
                </div>
            </div>

           {{-- @include('_components/forms/edit-footer', ['cancel_url' => '/kyc-list', 'update' => true ])--}}

        </footer>
    </section>
    {!! Form::close() !!}
@endsection
