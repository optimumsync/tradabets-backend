@extends('_layouts.master')

@section('main-title', 'User List')

@section('main-content')

    <section class="card">

        <div class="card-body">

            <div class="col-lg-12">
                <table class="table table-responsive-lg table-bordered table-striped mb-0" id="datatable-default">
                    <thead>
                    <th class="is-status">First Name</th>
                    <th class="is-status">Last Name</th>
                    <th class="is-status">DOB</th>
                    <th class="is-status">Email</th>
                    <th class="is-status">Phone</th>
                    <th class="is-status">City</th>
                    <th class="is-status">State</th>
                    <th class="is-status">Created At</th>
                    </thead>
                    <tbody>
                    @foreach($user_list as $row)
                        <tr>
                            <td>{{$row->first_name}}</td>
                            <td>{{$row->last_name}}</td>
                            <td>{{$row->date_of_birth}}</td>
                            <td>{{$row->email}}</td>
                            <td>{{$row->country_code}}{{$row->phone}}</td>
                            <td>{{$row->city}}</td>
                            <td>{{$row->state}}</td>
                            <td>{{$row->created_at}}</td>
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
                <a href="{{ url('kyc-upload-form') }}" class="btn btn-primary" id="txtEdit">Add</a>
            </div>
        </div>
        </footer>
    </section>

@endsection
