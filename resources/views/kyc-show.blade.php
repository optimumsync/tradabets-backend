@extends('_layouts.master')

@section('main-title', 'KYC Document List')

@section('main-content')

    <section class="card">

        <div class="card-body">

            <div class="col-lg-12">
                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                    <th class="is-status">Name</th>
                    <th class="is-status">Document</th>
                    <th class="is-status">Document Type</th>
                    <th class="is-status">Status</th>
                    <th class="is-status">Uploaded Date</th>
                    <th class="is-status">Action</th>
                    </thead>
                    <tbody>
                        @foreach($images as $row)
                    <tr>
                        <td>{{$row->user->first_name}}</td>
                        <td><img src="/document-show/{{$row->id}}" class="img-thumbnail" width="75" /></td>
                        <td>{{$row->document_type}}</td>
                        <td>{{$row->status}}</td>
                        <td>{{$row->created_at}}</td>
                        <td>
                        <a href="/kyc-list/view/{{$row->id}}">View Details</a></td>
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
