@extends('_layouts.master')

@section('main-title', 'Bet List')

@section('main-content')

    <section class="card">
    
        <div class="card-body">

            <div class="col-lg-12 mb-4 date-filter-box">

                {!! Form::open(['url' => '/betlist','class' => 'form-inline is-dashboard-filter-form mb-2', 'method' => 'get','onsubmit'=>'return validDate()']) !!}
                @if(Auth::user()->role === 'admin' && isset($users))
                    <div class="form-group mr-2 w-100">
                        <label for="user_id" class="mr-2">User:</label>
                        <!-- <select name="user_id" id="user_id" class="form-control form-control-sm">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (isset($filter_arr['user_id']) && $filter_arr['user_id'] == $u->id) ? 'selected' : '' }}>
                                    {{ $u->first_name }} {{ $u->last_name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select> -->
                        <select name="user_id" id="user_id" class="form-control form-control-sm user-select col-9">
                            <option value="">All Users</option>
                            @foreach($users as $u)
                                <option value="{{ $u->id }}" {{ (isset($filter_arr['user_id']) && $filter_arr['user_id'] == $u->id) ? 'selected' : '' }}>
                                    {{ $u->first_name }} {{ $u->last_name }} ({{ $u->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <br>
                <br>
                <br>
                
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
                    <th class="is-status">Betslip</th>
                    <th class="is-status">Bet Type</th>
                    <th class="is-status">Bet Date</th>
                    <th class="is-status">Result Date</th>
                    <th class="is-status">Amount</th>
                    <th class="is-status">Outcome</th>
                    <th class="is-status">Winning</th>
                    <!-- <th class="is-status">Settled Bets</th> -->
                    </thead>
                    <tbody>
                        @if(!empty($betList) && is_array($betList))
                            @foreach($betList as $bet)
                                <tr>
                                    <td>{{ $bet['bet_slip'] }}</td>
                                    <td>{{ ucfirst($bet['bet_type']) }}</td>
                                    <td>{{ $bet['bet_date'] }}</td>
                                    <td>{{ $bet['result_date'] }}</td>
                                    <td>{{ $bet['amount'] }}</td>
                                    <td>{{ $bet['outcome'] }}</td>
                                    <td>{{ $bet['winning'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">No bets found.</td>
                            </tr>
                        @endif
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
