@extends('_layouts.master')

@section('main-title', 'Balance List')

@section('main-content')

    <section class="card">

        <div class="card-body">


                <div class="col-lg-6">

                    {!! Form::open(['url' => '/balance-view','class' => 'form-horizontal is-dashboard-filter-form', 'method' => 'get','onsubmit'=>'return validDate()']) !!}
                    {{--{{ Form::select('', ['' => 'All'] + $users,$filter_arr['user'],['class' => 'form-control']) }}--}}
                    {{--{{ Form::select_md6('User:', 'form[user]',['' => 'All'] + $users, $filter_arr['user']) }}--}}
                    {{ Form::text_md6('User:', 'form[user]',$filter_arr['user']) }}
                    <div class="text-right p-1">
                        {{ Form::submit('Filter', ['class' => 'btn btn-sm btn-primary']) }}
                    </div>

                </div>

                {!! Form::close() !!}


            <hr>

            <div class="col-lg-12">
                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                    <th class="is-status">Name</th>
                    <th class="is-status">Balance</th>
                    </thead>
                    <tbody>
                    @foreach($balance as $row)
                        <tr>
                            <td>{{$row->user->first_name}} {{$row->user->last_name}}</td>
                            <td>{{$row->balance}}</td>
                        </tr>
                    @endforeach
                    </tbody>
            </div>
        </div>

    </section>

@endsection
