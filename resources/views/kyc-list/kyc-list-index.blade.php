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
                    <th class="is-status">Document Number</th>
                    <th class="is-status">Uploaded Date</th>
                    <th class="is-status">Status</th>
                    </thead>
                    <tbody>
                    @foreach($kyc_list as $row)
                        <tr>
                            <td>{{$row->name}}</td>
                            <td><img src="/document-show/{{$row->id}}" class="img-thumbnail" width="75" /></td>
                            <td>{{$row->document_type}}</td>
                            <td>{{$row->id_number}}</td>
                            <td>{{$row->created_at}}</td>
                            <td>{{$row->status}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <footer class="card-footer">
        <div class="row">
            <div class="col-md-6">
                <a href="#" class="btn btn-default">Cancel</a>
            </div>
            <div class="col-md-6 text-right">
                <a href="{{ url('home') }}" class="btn btn-primary" id="txtEdit">Add</a>
            </div>
        </div>

        </footer>
    </section>

@endsection
