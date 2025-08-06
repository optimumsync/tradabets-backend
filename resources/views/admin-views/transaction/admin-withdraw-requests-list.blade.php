@extends('_layouts.master')

@section('main-title', 'Withdraw Request List')

@section('main-content')
</br>
<!-- <div id="message-area"> -->
    @if (session('transfer-success'))
        <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('transfer-success') }}
        </div>
    @elseif (session('success'))
        <div class="alert alert-success">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('success') }}
        </div>
    @elseif (session('error'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('error') }}
        </div>
    @elseif (session('error1'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('error1') }}
        </div>
    @elseif (session('error2'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('error2') }}
        </div>
    @elseif (session('error3'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('error3') }}
        </div>
    @elseif (session('error4'))
        <div class="alert alert-danger">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('error4') }}
        </div>        
    @endif
<!-- </div> -->

    <div class="row">
        <div class="col-lg-8">
            @if (session('list_updated'))
                <div class="alert alert-success">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('list_updated') }}
                </div>
            @elseif (session('list_update_failed'))
                <div class="alert alert-danger">
                <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a> {{ session('list_update_failed') }}
                </div>
            @endif
        </div>
        <div class="col-lg-4 text-right p-1">
            <input class="btn btn-primary" style="font-size: smaller;" type="button" value="Update Banks List" onclick="location.href='/updateBanksList'">
        </div>
    </div>
    
    <section class="card">

        <div class="card-body">

            <div class="col-lg-12">
                <div class="row form-view-row">
                    <div class="col-xs-4 col-md-4 fnt-b"></div>
                    <div class="col-xs-8 col-md-8"><span class="error-message"></span></div>
                </div>
              <form id="withdraw-request-lists-form" method="POST">
                <table class="table table-responsive-lg table-bordered table-striped mb-0 withdraw_table" id="datatable-default" >
                    <thead>
                            <th class="bulkCheckbox nummer"><input type="checkbox" class="toggleCheckbox" name="checkProducts" onclick="checkAll('#datatable-default', this)" />Select All</th>
                            <th class="is-status">Name</th>
                            <th class="is-status">Amount</th>
                            <th class="is-status">Status</th>
                            <th class="is-status">Date</th>
                            <th class="is-status">Action</th>
                            <th><div class="approve_reject_all"><input class="btn btn-primary" style="font-size: smaller;" type="button" value="Approve Selected" onclick="ApproveSelected()">
<!--                            <input class="btn btn-danger" style="font-size: smaller;" type="button" value="Reject Selected" onclick="RejectSelected()"></div></th> -->
                    </thead>
                    
                    <tbody>
                        @foreach($withdraw_requests as $row)
                            <tr>
                                <td><input class="bulkCheckbox" type="checkbox" name="select_request" class="select_request" value="{{$row->id}}"/></td>
                                <td>{{$row->user->first_name}} {{$row->user->last_name}}</td>
                                <td>{{$row->amount}}</td>
                                <td>{{$row->status_description}}</td>
                                <td>{{$row->created_at}}</td>

                                <td><a href="/withdraw-request/view/{{$row->id}}">View Details</a></td>

                                <td>
                                <div class="approve_reject"><input class="btn btn-primary" style="font-size: smaller;" type="button" value="Approve" onclick="ApproveRow('/initiate_transaction/{{$row->id}}');">
                                     <input class="btn btn-danger" style="font-size: smaller;" type="button" value="Reject" onclick="RejectRow('/withdraw-request-individual-reject/update/{{$row->id}}');">
                                </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>

              </form>
            </div>
        </div>

    </section>

@endsection

