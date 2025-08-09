@extends('_layouts.master')

@section('main-title', 'Withdrawal List')

@section('main-content')

<style>
    /* Custom CSS for the attractive tables */
    .table-responsive {
        border: none;
        box-shadow: 0 5px 15px -5px rgba(0, 0, 0, 0.1);
        border-radius: 15px;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table {
        margin-bottom: 0;
        background-color: #fff;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 15px;
    }

    .table thead th {
        border-top: none;
        border-bottom: 2px solid #e9ecef;
        background-color: #f6f6f6;
        font-weight: 600;
        color: #555;
        padding: 1.5rem;
        vertical-align: middle;
        text-align: left;
    }
    
    .table thead tr th:first-child {
        border-top-left-radius: 15px;
    }

    .table thead tr th:last-child {
        border-top-right-radius: 15px;
    }

    .table tbody tr {
        transition: background-color 0.2s ease-in-out;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .table tbody td {
        border-top: 1px solid #e9ecef;
        padding: 1.5rem;
        vertical-align: middle;
        color: #555;
        font-weight: 400;
    }

    .table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .table tbody tr:last-child td:first-child {
        border-bottom-left-radius: 15px;
    }
    
    .table tbody tr:last-child td:last-child {
        border-bottom-right-radius: 15px;
    }

    .status-badge {
        font-weight: 600;
        padding: 0.5em 1.2em;
        border-radius: 9999px;
        font-size: 0.8em;
        text-transform: uppercase;
        display: inline-block;
        min-width: 100px;
        text-align: center;
    }
    
    .status-badge.pending {
        background-color: #ffc107;
        color: #856404;
    }
    
    .status-badge.approved {
        background-color: #28a745;
        color: #fff;
    }
    
    .status-badge.rejected {
        background-color: #dc3545;
        color: #fff;
    }
    
    .status-badge.reversed {
        background-color: #6c757d;
        color: #fff;
    }
    
    .tab-content {
        background: transparent;
        border: none;
        box-shadow: none;
        padding: 1.5rem 0;
    }

    .nav-tabs .nav-link {
        font-weight: 600;
        color: #777;
    }
    
    .nav-tabs .nav-link.active {
        color: #33353F;
        border-bottom: 3px solid #CCC;
    }

    @media (max-width: 767px) {
        .table thead {
            display: none;
        }

        .table tbody td {
            display: block;
            text-align: left;
            padding-left: 50%;
            position: relative;
            word-wrap: break-word;
        }

        .table tbody td:before {
            content: attr(data-label);
            position: absolute;
            top: 50%;
            left: 15px;
            width: 45%;
            font-weight: 600;
            color: #33353F;
            transform: translateY(-50%);
        }
        
        .table tbody tr:first-child td:first-child {
            border-top-left-radius: 15px;
            border-top-right-radius: 15px;
        }

        .table tbody tr:last-child td:last-child {
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }
    }
</style>

<div class="container">
    <div class="card card-admin">
        <div class="card-body">
           
            <ul class="nav nav-tabs" id="myTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="all-tab" data-toggle="tab" href="#all" role="tab" aria-controls="all" aria-selected="true">All</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="pending-tab" data-toggle="tab" href="#pending" role="tab" aria-controls="pending" aria-selected="false">Pending</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab" aria-controls="approved" aria-selected="false">Approved</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="rejected-tab" data-toggle="tab" href="#rejected" role="tab" aria-controls="rejected" aria-selected="false">Rejected</a>
                </li>
            </ul>

            <div class="tab-content" id="myTabContent">
                <!-- All Withdrawal Requests -->
                <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawals as $withdrawal)
                                <tr>
                                    <td data-label="Name">{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</td>
                                    <td data-label="Email">{{ $withdrawal->user->email }}</td>
                                    <td data-label="Amount">{{ $withdrawal->amount }}</td>
                                    <td data-label="Status">
                                        <span class="status-badge {{ $withdrawal->status }}">
                                            {{ $withdrawal->status }}
                                        </span>
                                    </td>
                                    <td data-label="Date">{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Pending Withdrawal Requests -->
                <div class="tab-pane fade" id="pending" role="tabpanel" aria-labelledby="pending-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawals as $withdrawal)
                                @if($withdrawal->status == 'pending')
                                <tr>
                                    <td data-label="Name">{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</td>
                                    <td data-label="Email">{{ $withdrawal->user->email }}</td>
                                    <td data-label="Amount">{{ $withdrawal->amount }}</td>
                                    <td data-label="Status">
                                        <span class="status-badge pending">
                                            {{ $withdrawal->status }}
                                        </span>
                                    </td>
                                    <td data-label="Date">{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Approved Withdrawal Requests -->
                <div class="tab-pane fade" id="approved" role="tabpanel" aria-labelledby="approved-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawals as $withdrawal)
                                @if($withdrawal->status == 'approved')
                                <tr>
                                    <td data-label="Name">{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</td>
                                    <td data-label="Email">{{ $withdrawal->user->email }}</td>
                                    <td data-label="Amount">{{ $withdrawal->amount }}</td>
                                    <td data-label="Status">
                                        <span class="status-badge approved">
                                            {{ $withdrawal->status }}
                                        </span>
                                    </td>
                                    <td data-label="Date">{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rejected Withdrawal Requests -->
                <div class="tab-pane fade" id="rejected" role="tabpanel" aria-labelledby="rejected-tab">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($withdrawals as $withdrawal)
                                @if($withdrawal->status == 'rejected')
                                <tr>
                                    <td data-label="Name">{{ $withdrawal->user->first_name }} {{ $withdrawal->user->last_name }}</td>
                                    <td data-label="Email">{{ $withdrawal->user->email }}</td>
                                    <td data-label="Amount">{{ $withdrawal->amount }}</td>
                                    <td data-label="Status">
                                        <span class="status-badge rejected">
                                            {{ $withdrawal->status }}
                                        </span>
                                    </td>
                                    <td data-label="Date">{{ $withdrawal->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Script to handle tab switching
    $(document).ready(function() {
        $('#myTab a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
        });
    });
</script>
@endpush