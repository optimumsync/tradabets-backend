@extends('_layouts.master')

@section('main-title', 'Payment Transaction Report')

@section('main-content')

    <section class="card">

        <div class="card-body">

            <div class="col-lg-12">

                <div class="col-lg-6">

                    {!! Form::open(['url' => '/transaction-report','class' => 'form-horizontal is-dashboard-filter-form', 'method' => 'get','onsubmit'=>'return validDate()']) !!}


                    {{ Form::text_md6('Date From:', 'form[date_from]',$filter_arr['date_from'], [
                                            'class' => 'form-control form-control-sm','id'=>'start_id'
                                        ]) }}

                    {{ Form::text_md6('Date To:', 'form[date_to]',$filter_arr['date_to'], [
                                            'class' => 'form-control form-control-sm','id'=>'endDate_id'
                                        ]) }}
                </div>
                <div class="col-lg-6">
                    <div class="text-right p-1">
                        {{ Form::submit('Filter', ['class' => 'btn btn-sm btn-primary']) }}
                    </div>
                </div>

                {!! Form::close() !!}
          <hr>

                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                        <th class="is-status">Date</th>
                        <th class="is-status">User ID</th>
                        <th class="is-status">User Name</th>

                        <th class="is-status">Amount</th>
                        <th class="is-status">Status</th>
                        <th class="is-status">Transaction Reference</th>
                        <th class="is-status">Recipient Code</th>
                        <th class="is-status">Transaction Code</th>
                    </thead>
                    <tbody>
                        @foreach($payment as $row)
                            <tr>
                                <td>{{$row->payment_at}}</td>
                                <td>{{$row->user_id}}</td>
                                <td>{{$row->username}}</td>
                                <td>{{$row->amount}}</td>
                                <td>{{$row->status}}</td>
                                <td>{{$row->transaction_reference}}</td>
                                <td>{{$row->recipient_code}}</td>
                                <td>{{$row->transaction_code}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>  

            </div>
        </div>

    </section>

@endsection
