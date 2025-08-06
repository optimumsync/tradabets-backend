@extends('_layouts.master')

@section('main-title', 'Dashboard')

@section('main-content')



    <section class="card">

        <div class="card-body">


        <!--<div class="card-body">

            <div class="row">
                 <div class="col-3">

                    <section class="card card-featured-left card-featured-primary m-0">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-primary">
                                        <i class="fas fa-life-ring"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">Support Questions</h4>
                                        <div class="info">
                                            <strong class="amount">1281</strong>
                                            <span class="text-primary">(14 unread)</span>
                                        </div>
                                    </div>

                                    <div class="summary-footer">
                                        <a class="text-muted text-uppercase" href="#">(view all)</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
                <div class="col-3">

                    <section class="card card-featured-left card-featured-secondary m-0">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-secondary">
                                        <i class="fas fa-cc-visa"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">Total Profit</h4>
                                        <div class="info">
                                            <strong class="amount">$ 14,890.30</strong>
                                        </div>
                                    </div>
                                    <div class="summary-footer">
                                        <a class="text-muted text-uppercase" href="#">(withdraw)</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
                <div class="col-3">

                    <section class="card card-featured-left card-featured-tertiary m-0">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-tertiary">
                                        <i class="fas fa-shopping-cart"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">Today's Orders</h4>
                                        <div class="info">
                                            <strong class="amount">38</strong>
                                        </div>
                                    </div>
                                    <div class="summary-footer">
                                        <a class="text-muted text-uppercase" href="#">(statement)</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
                <div class="col-3">

                    <section class="card card-featured-left card-featured-quaternary m-0">
                        <div class="card-body">
                            <div class="widget-summary">
                                <div class="widget-summary-col widget-summary-col-icon">
                                    <div class="summary-icon bg-quaternary">
                                        <i class="fas fa-user"></i>
                                    </div>
                                </div>
                                <div class="widget-summary-col">
                                    <div class="summary">
                                        <h4 class="title">Today's Visitors</h4>
                                        <div class="info">
                                            <strong class="amount">3765</strong>
                                        </div>
                                    </div>
                                    <div class="summary-footer">
                                        <a class="text-muted text-uppercase" href="#">(report)</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                </div>
            </div>

        </div> -->
<!--
        <div class="col-lg-6">
             <button class='btn btn-md btn-success' onclick="location.href='/disableOTP'">Disable OTP for Transfers</button>
             <br>
        </div> -->

            <div class="col-lg-12 mb-4 date-filter-box">

                {!! Form::open(['url' => '/home','class' => 'form-inline is-dashboard-filter-form', 'method' => 'get','onsubmit'=>'return validDate()']) !!}

                {{ Form::text_md6('Date From:', 'form[date_from]',$filter_arr['date_from'], [
                                        'class' => 'form-control form-control-sm','id'=>'start_id'
                                    ]) }}

                {{ Form::text_md6('Date To:', 'form[date_to]',$filter_arr['date_to'], [
                                        'class' => 'form-control form-control-sm','id'=>'endDate_id'
                                    ]) }}

                <div class="form-group row">
                    {{ Form::submit('Filter', ['class' => 'btn btn-sm btn-primary ml-5']) }}
                    <span id="date-error" class="ml-4"></span>
                </div>

                {!! Form::close() !!}
    
            </div>


             <div class="col-lg-12">
                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                        <th class="is-status">Opening Balance</th>
                        <th class="is-status">Amount</th>
                        <th class="is-status">Status</th>
                        <th class="is-status">Closing Balance</th>
                        <th class="is-status">Remarks</th>
                        <th class="is-status">Date</th>
                    </thead>
                    <tbody>
                    @foreach($transaction as $row)
                    <tr>
                       <td>{{$row->opening_balance}}</td>
                                @if($row->status!='withdraw')
                                <td class='deposit-amount'>+{{$row->amount}}</td>
                                @else
                                 <td class='withdraw-amount'>-{{$row->amount}}</td>
                                 @endif
                                <td>{{$row->status_description}}</td>
                                <td>{{$row->closing_balance}}</td>
                                <td>{{$row->remarks}}</td>
                                <td>{{$row->created_at}}</td>
                   </tr>
                    @endforeach
                    </tbody>
                </table>
             </div>
        </div>
    </section>

@endsection
