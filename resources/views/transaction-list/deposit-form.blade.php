@extends('_layouts.master')

@section('main-title', 'Deposit')

@section('main-content')

    <section class="card">

        <div class="card-body">

            <div class="col-lg-6 mb-3">
                <div class="fnt-b fnt-s-16">Available Balance :</div>
                <div class="fnt-s-24 fnt-b mt-1">₦ {{$avail_balance}}</div>
            </div>

            <div class="row pl-3">
                <div class="col-sm-5">
                        {!! Form::open(['url' => '/payment-request','id' => 'paypalForm','class' => 'form-horizontal is-dashboard-filter-form', 'method' => 'get','onsubmit'=>'return depositAmountValidation()']) !!}

                        <div class="form-group">
                            <label for="exampleInputEmail1" class="fnt-b">Amount</label>
                            <input class="form-control deposit_amount col-md-11" id="deposit_block" required="" onkeypress="return (event.charCode == 8 || event.charCode == 0 || event.charCode == 13) ? null : event.charCode >= 48 &amp;&amp; event.charCode <= 57" onkeyup="amountValidationMessageClear()" maxlength="7" name="deposit_amount" type="text" value="100">
                        </div>

                        <button type="button" class="btn btn-light btn-amount" data-amount="100">+ ₦100</button>
                        <button type="button" class="btn btn-light btn-amount ml-2" data-amount="500">+ ₦500</button>
                        <button type="button" class="btn btn-light btn-amount ml-2" data-amount="1000">+ ₦1000</button>

                        <div class="row form-view-row">
                            <div class="col-xs-4 col-md-4 fnt-b"></div>
                            <div class="col-xs-8 col-md-8"><span class="amount-validation-message"></span></div>
                        </div>


                        {!! Form::close() !!}
                        <br>
                        <div class="text-left p-1">
                            <button class="btn btn-lg btn-primary" onclick="depositclick();" value="Deposit">
                                Deposit
                            </button>
                        </div>


                        {{--  PAYSTACK  --}}
                        <form method="POST" onsubmit="paystackFunction()" action="{{ route('pay') }}" id="paymentForm" accept-charset="UTF-8" class="form-horizontal" role="form">
                            <div class="row" style="margin-bottom:0px;">
                                <div class="col-md-12 col-md-offset-2">

                                    <input type="hidden" name="email" value="tradabets@test.com">
                                    <input type="hidden" name="orderID" value="">
                                    <input type="hidden" name="amount" id="paystack_amount" value="">
                                    <input type="hidden" name="quantity" value="1">
                                    <input type="hidden" name="currency" value="NGN">
                                    <input type="hidden" name="metadata" value="{{ json_encode($array = ['key_name' => 'value',]) }}">
                                    <input type="hidden" name="reference" value="{{ Paystack::genTranxRef() }}">
                                    {{ csrf_field() }}

                                    <input type="hidden" name="metadata" value="{{ json_encode($array = [ 'amount' => auth::user()->amount ]) }}">

                                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                    {{--                                            <div class="text-right p-1">--}}
                                    {{--                                                <button class="btn btn-sm btn-primary" type="submit" value="Deposit with Paystack!">--}}
                                    {{--                                                    Deposit with Paystack--}}
                                    {{--                                                </button>--}}
                                    {{--                                            </div>--}}
                                </div>
                            </div>
                        </form>


                        {{--  PAYSTACK  --}}



                        {{--  FLUTTERWAVE  --}}

                        <form method="POST" onsubmit="flutterwaveFunction()" action="{{ route('flutterwave_pay') }}" id="paymentForm_flutterwave">
                            {{ csrf_field() }}

                            <input type="hidden" name="name"/>
                            <input type="hidden" name="email" type="email"/>
                            <input type="hidden" name="phone" type="tel"/>
                            <input type="hidden" name="amount" id="flutterwave_amount" value="">
                            <input type="hidden" name="transactionReference" value="{{ Quickteller::generateTransactionReference() }}">

                            <!-- <input type="submit" value="Buy" /> -->
                        </form>

                        {{--  FLUTTERWAVE  --}}


                        {{--  interswitch  --}}
                        <form method="POST" onsubmit="interswitchFunction()" action="{{ route('interswitch-pay') }}" id="paymentForm_interswitch">
                            {{ csrf_field() }}
                            <input type="hidden" name="amount" id="interswitch_amount" value="">
                            <input type="hidden" name="transactionReference" value="{{ Quickteller::generateTransactionReference() }}">
                        </form>
                        {{--  interswitch  --}}


                        {{--  Opay  --}}
                        <form method="POST" onsubmit="opayFunction()" action="{{ route('opay-pay') }}" id="paymentForm_opay">
                            {{ csrf_field() }}
                            <input type="hidden" name="amount" id="opay_amount" value="">
                            <input type="hidden" name="transactionReference" value="{{ Quickteller::generateTransactionReference() }}">
                        </form>
                        {{--  Opay  --}}

                </div>
                <div class="col-sm">
                    <label class="font-weight-bold">Choose Payment Method</label>
                    <div class="row">
                        <div class="col-md-6">
                            <input class="radio" type="radio" name="payment" id="paystk" value="paystack" checked /> <span>Paystack</span><br>
                            <input class="radio" type="radio" name="payment" id="flutterwave" value="flutterwave" /> <span>Flutterwave</span><br>
                            <!-- <input class="radio" type="radio" name="payment" id="interswitch" value="interswitch" /> <span>Quickteller</span><br>
                            <input class="radio" type="radio" name="payment" id="opay" value="opay" /> <span>Opay</span> -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- <button type="button" onClick="submitData()">Pay with Arca</button> -->

        <script>
            function submitData() {
                const generatedId = Math.random().toString();
                // const transactionId = generatedId.replace('0.', '');
                const transactionId = 'transactID123';

                const hashBody = {
                    transactionId: transactionId,
                    amount: 100,
                    currency: 'NGN',
                    country: 'NG',
                    email: 'ochuko@arcapayments.com',
                    phoneNumber: '0803294342',
                    firstName: 'Elvis',
                    lastName: 'Ochuko',
                    callbackUrl: '"http://tradabets.com:8000/"',
                };
                let hashedPayload = '';
                const keys = Object.keys(hashBody).sort();
                for (const index in keys) {
                    let key = '';
                    key = keys[index];
                    hashedPayload += hashBody[key];
                }
                const hashString = 'ARCPUBK-66dbf243d7fe04168d7d2af4892d274d-X' + hashedPayload;
                const hash = sha256.create();
                hash.update(hashString);
                const hashStringFinal =  hash.hex();
                const body = {
                    "publicKey": "ARCPUBK-66dbf243d7fe04168d7d2af4892d274d-X",
                    "logoURL": "",
                    "transactionId": transactionId,
                    "amount": 100,
                    "currency": "NGN",
                    "country": "NG",
                    "email": 'ochuko@arcapayments.com',
                    "phoneNumber": '0803294342',
                    "firstName": 'Elvis',
                    "lastName": 'Ochuko',
                    "hash": hashStringFinal,
                     "meta": JSON.stringify([{
                        "metaName": "GOTV NUMBER",
                        "metaValue": "075632148963"
                    }]),

                    "callbackUrl": "http://tradabets.com:8000/",
                   onClose: function (response) {
                        console.log(JSON.stringify(response))
                    },
                    onSuccess: function (response) {
                        console.log(JSON.stringify(response))
                    },
                    onError: function (response) {
                        console.log(JSON.stringify(response))
                    }
                }
                arcapayCheckout(body);
            }

            $('.btn-amount').click(function(){
                $('#deposit_block').val($(this).data('amount'));
            });
        </script>

        </div>

    </section>

@endsection
