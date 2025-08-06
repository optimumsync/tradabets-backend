@extends('_layouts.master')

@section('main-title', 'Withdraw')

@section('main-content')

    <section class="card mt-4">

        <div class="card-body">

            <div class="col-lg-6 mb-3">
                <div class="fnt-b fnt-s-16">Available balance to withdraw:</div>
                <div class="fnt-s-24 fnt-b mt-1 balance-amount">₦ {{$avail_balance}}</div>
                <input type="hidden" id="balance-amount" value="{{$avail_balance}}">
            </div>
            <div class="col-lg-6 mb-3">
                <div class="fnt-b fnt-s-16">Total Amount :</div>
                <div class="fnt-s-20 fnt-b mt-1">₦ {{ $total_amount ?? 0 }}</div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="fnt-b fnt-s-16">Total Bonus Amount :</div>
                <div class="fnt-s-20 fnt-b mt-1">₦ {{ $total_bonus ?? 0 }}</div>
            </div>
            <div class="col-lg-6 mb-3">
                <div class="fnt-b fnt-s-16">Total Winning Amount :</div>
                <div class="fnt-s-20 fnt-b mt-1">₦ {{ $total_winning ?? 0 }}</div>
            </div>
            <div class="col-lg-6">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                    </ul>
                </div>
            @endif

            

                {!! Form::open(['url' => '/withdraw-request','class' => 'form-horizontal is-dashboard-filter-form', 'method' => 'get', 'onsubmit'=>'return amountValidation()']) !!}

                <div class="form-group">
                    <label for="exampleInputEmail1" class="fnt-b">Amount</label>
                    <input class="form-control withdraw-amount-input" onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13) ? null : event.charCode >= 48 &amp;&amp; event.charCode <= 57" onkeyup="amountValidationMessageClear()" name="withdraw_amount" type="text" value="" id="withdraw_amount">
                </div>

                <div class="row form-view-row">
                    <div class="col-xs-8 col-md-8"><span class="amount-validation-message"></span></div>
                    <div class="col-xs-8 col-md-8"><span class="kyc-status-message"></span></div>
                </div>

                <div class="p-1">
                    @php $kyc_status=Session::get('kyc_status'); @endphp
                    @php $account_status=Session::get('account_status'); @endphp
                    
                {{ Form::hidden('kycstatus', $kyc_status, ['id' => 'kycstatus']) }}
                {{ Form::hidden('accountstatus', $account_status, ['id' => 'accountstatus']) }}
                {{ Form::hidden('bet_count', $bet_count, ['id' => 'bet_count']) }}
                {{ Form::submit('Withdraw', ['class' => 'btn btn-lg btn-primary']) }}
                  <!-- {{ ($kyc_status == 1)?  Form::submit('Request', ['class' => 'btn btn-sm btn-primary']) :   Form::submit('Request', ['class' => 'btn btn-sm btn-primary','disabled']) }} -->

                </div>
                {!! Form::close() !!}

            </div>
        </div>

    </section>

@endsection
