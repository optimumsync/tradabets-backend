@extends('_layouts.master')

@section('main-title', 'Transaction List')

@section('main-content')

    <section class="card">

        <div class="card-body">

            <div class="col-lg-12 mb-4 date-filter-box">

                {!! Form::open(['url' => '/transaction','class' => 'form-inline is-dashboard-filter-form mb-2', 'method' => 'get','onsubmit'=>'return validDate()']) !!}

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
                    <th class="is-status">Transaction ID</th>
                    <th class="is-status">Reference</th>
                    <th class="is-status">Opening Balance</th>
                    <th class="is-status">Amount</th>
                    <th class="is-status">Status</th>
                    <th class="is-status">Closing Balance</th>
                    <th class="is-status">Remarks</th>
                    <th class="is-status">Transaction Type</th>
                    <th class="is-status">Date</th>
                    </thead>
                    <tbody>
                      @foreach($transaction as $row)
                    <tr>
                        <?php
                            $reference = json_decode($row->request);
                            if(isset($reference->data->reference)) {
                                $ref = $reference->data->reference;
                            }elseif (isset($reference->transactionReference)) {
                                $ref = $reference->transactionReference;
                            }else {
                                $ref = '';
                            }
                        ?>
                        <td>{{$row->id}}</td>
                        <td>{{$ref}}</td>
                        <td>{{$row->opening_balance}}</td>
                        @if($row->status == 'withdraw' || $row->status == 'approved')
                            <td class='withdraw-amount'>-{{$row->amount}}</td>
                        @else
                            <td class='deposit-amount'>+{{$row->amount}}</td>
                        @endif
                        <td>{{$row->status_description}}</td>
                        <td>{{$row->closing_balance}}</td>
                        <td>{{$row->remarks}}</td>
                        <td>{{ ucfirst($row->transaction_type) }}</td>
                        <td>{{$row->created_at}}</td>
                    </tr>
                     @endforeach
                    </tbody>
                </table>
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
