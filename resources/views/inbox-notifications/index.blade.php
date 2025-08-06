@extends('_layouts.master')

@section('main-title', 'Inbox')

@section('main-content')

    <section class="card">

        <div class="card-body">


            <div class="col-lg-12">
                <table class="table table-responsive-lg table-bordered  mb-0" id="datatable-default">
                    <thead>
                    <th class="is-status">Subject</th>
                    <th class="is-status">Action</th>
                    </thead>
                    <tbody>
                      @foreach($inbox_notificationrs as $row)
                    <tr>
                        @if($row->read_at!=null)
                        <td>{{$row->subject}}</td>
                        @else
                            <td><div style="display: flex;" ><div class="unread-class"><strong>{{$row->subject}}</strong></div><div> <i class="fa fa-circle unread-icon"></i></div></div></td>
                        @endif
                        <td>
                             <a href="/inbox/message-view/{{$row->inbox_notification_id}}" class="btn btn-primary">Read</a>
                        </td>
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
