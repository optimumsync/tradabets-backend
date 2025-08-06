@extends('_layouts.master')

@section('main-title', 'Bank Accounts')

@section('main-content')
</br>
    @if (session('status'))
        <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('status') }}
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('error') }}
        </div>
    @elseif (session('errors'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('errors') }}
        </div>
    @endif

    <section class="card mt-1">

        <div class="card-body">

            <div class="col-lg-12 mb-4 date-filter-box">

                {!! Form::open(['url' => '/bank-accounts','class' => 'form-inline is-dashboard-filter-form mb-2', 'method' => 'get','onsubmit'=>'return validDate()']) !!}

                {{ Form::text_md6('Date From:', 'form[date_from]',$filter_arr['date_from'], [
                                        'class' => 'form-control form-control-sm','id'=>'start_id'
                                    ]) }}

                {{ Form::text_md6('Date To:', 'form[date_to]',$filter_arr['date_to'], [
                                        'class' => 'form-control form-control-sm','id'=>'endDate_id'
                                    ]) }}

                <div class="form-group row">
                    {{ Form::submit('Filter', ['class' => 'btn btn-sm btn-primary ml-5']) }}
                </div>

                {!! Form::close() !!}

                <span id="date-error"></span>

            </div>

            <div class="col-lg-12">
                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                    <th class="is-status">Account Name</th>
                    <th class="is-status">Account Number</th>
                    <th class="is-status">Bank Name</th>
                    <th class="is-status">Bank Code</th>
                    <!-- <th class="is-status">BVN Number</th> -->
                    <th class="is-status">Active</th>
                    </thead>
                    <tbody>
                        @foreach($bank_list as $row)
                            <tr>
                                <td>{{$row->account_name}}</td>
                                <td>{{$row->account_number}}</td>
                                <td>{{$row->bank_name}}</td>
                                <td>{{$row->bank_code}}</td>
                                <!-- <td>{{$row->BVN_Number}}</td> -->
                                <td>{{$row->Active_status}}</td>

                            </tr>
                        @endforeach
                    </tbody>

                </table>
            </div>
        </div>
{{--        <footer class="card-footer">--}}
{{--            <div class="row">--}}
{{--                <div class="col-md-12 text-right">--}}
{{--                    <a href="{{ url('add-bank-account') }}" class="btn btn-primary" id="txtEdit">Add Bank Account</a>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </footer>--}}
<!-- ------------- -->
     <footer class="card-footer">
          <div class="row">
              <div class="col-md-12 text-right">
                   <a href="{{ url('add-bank-account') }}" class="btn btn-primary" id="txtEdit">Add Bank Account</a>
            </div>
            </div>
        </footer>
    </section>

@endsection
