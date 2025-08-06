@extends('_layouts.master')

@section('main-title', 'Withdraw')

@section('main-content')

    <section class="card">
        <header class="card-header has-actions p-2" style="padding-left: 34px !important;">

            @include('_components/pages/card-actions', ['btn_link_arr' => [
                    '/withdraw-request-form' => 'New withdraw Request',
                ]])`

        </header>

        <div class="card-body">

            <div class="col-lg-12 mb-4 date-filter-box">

                {!! Form::open(['url' => '/withdraw','class' => 'form-inline is-dashboard-filter-form mb-2', 'method' => 'get','onsubmit'=>'return validDate()']) !!}

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
                    <th class="is-status">Amount</th>
                    <th class="is-status">Requested Date</th>
                    <th class="is-status">Status</th>
                    <th class='is-status'>Action</th>
                    </thead>
                    <tbody>
                      @foreach($withdraw as $row)
                    <tr>
                        <td>{{$row->amount}}</td>
                        <td>{{$row->created_at}}</td>
                        <td>{{$row->status_description}}</td>
                        @if($row->status=='pending')
                        <td><a href="/reverse-withdraw/{{$row->id}}" class="btn btn-primary"><i class="fa fa-undo"></i> Reverse Withdraw</a></td>
                        @else
                        <td></td>
                        @endif
                    </tr>
                     @endforeach
                    </tbody>
            </div>
        </div>
        <!-- <footer class="card-footer">
          <div class="row">
        <div class="col-md-6">
                <a href="/forms/area" class="btn btn-default">Cancel</a>
        </div>
        <div class="col-md-6 text-right">
                <a href="#" class="btn btn-primary" id="txtEdit">Add</a>
        </div>
    </div>

        </footer> -->
    </section>

@endsection
