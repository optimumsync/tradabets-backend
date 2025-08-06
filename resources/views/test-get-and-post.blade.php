@extends('_layouts.master')

@section('main-title', 'Bank Accounts')

@section('main-content')

    <section class="card">
    
        <div class="card-body">

            <div class="col-lg-12">

                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                    <th class="is-status">Account Name</th>
                    <th class="is-status">Account Number</th>
                    <th class="is-status">Bank Name</th>
                    </thead>
                    <tbody>
                @foreach($collection as $row)
                    <tr>
                        <td>{{$row->amount}}</td>
                        <td>{{$row->recipient_code}}</td>
                        <td>{{$row->reason}}</td>
                    </tr>
                @endforeach
                    </tbody>
                    
                </table>

            </div>
        </div>

    </section>

@endsection
