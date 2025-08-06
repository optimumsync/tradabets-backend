@extends('_layouts.master')

@section('main-title', 'Transaction List')

@section('main-content')

    <section class="card">

        <div class="card-body">

            <div class="col-lg-12 row">

                <div class="col-lg-6">

                    {!! Form::open(['url' => '/transaction-view','class' => 'form-horizontal is-dashboard-filter-form', 'method' => 'get','onsubmit'=>'return validDate()']) !!}


                    {{ Form::text_md6('Date From:', 'form[date_from]',$filter_arr['date_from'], [
                                            'class' => 'form-control form-control-sm','id'=>'start_id'
                                        ]) }}

                    {{ Form::text_md6('Date To:', 'form[date_to]',$filter_arr['date_to'], [
                                            'class' => 'form-control form-control-sm','id'=>'endDate_id'
                                        ]) }}
                </div>

                <div class="col-lg-6">
                    {{--{{ Form::select('', ['' => 'All'] + $users,$filter_arr['user'],['class' => 'form-control']) }}--}}
                    {{--{{ Form::select_md6('User:', 'form[user]',['' => 'All'] + $users, $filter_arr['user']) }}--}}
                    {{ Form::text_md6('User:', 'form[user]',$filter_arr['user']) }}

                    {{ Form::select_md6('Status:', 'form[status]',config('custom-form.transaction-type-arr'), $filter_arr['status']) }}

                    <div class="text-right p-1">
                        {{ Form::submit('Filter', ['class' => 'btn btn-sm btn-primary']) }}
                    </div>
                </div>

                {!! Form::close() !!}

            </div>

           <hr>

            <div class="col-lg-12">
                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                    <th class="is-status">Name</th>
                    <th class="is-status">Opening Balance</th>
                    <th class="is-status">Amount</th>
                    <th class="is-status">Status</th>
                    <th class="is-status">Closing Balance</th>
                    <th class="is-status">Date</th>
                    </thead>
                    <tbody>
                    @foreach($transaction as $row)
                        <tr>
                            <td>{{$row->user->first_name}} {{$row->user->last_name}}</td>
                            <td>{{$row->opening_balance}}</td>
                              @if($row->status!='withdraw')
                        <td class='deposit-amount'>+{{$row->amount}}</td>
                        @else
                         <td class='withdraw-amount'>-{{$row->amount}}</td>
                         @endif
                            <td>{{$row->status_description}}</td>
                            <td>{{$row->closing_balance}}</td>
                            <td>{{$row->created_at}}</td>
                        </tr>
                    @endforeach
                    </tbody>
            </div>
        </div>

    </section>

@endsection
