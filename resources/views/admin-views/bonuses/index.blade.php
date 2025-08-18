@extends('_layouts.master')
@section('main-title', 'Manage Bonuses')
@section('main-content')

<div class="row">
    {{-- Form to Create a New Bonus --}}
    <div class="col-lg-4">
        <section class="card card-admin">
            <header class="card-header">
                <h2 class="card-title">Add New Bonus</h2>
            </header>
            <form action="{{ route('admin.bonuses.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label for="name">Bonus Name <span class="text-danger">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" required
                            value="{{ old('name') }}">
                    </div>
                    <div class="form-group">
                        <label for="amount">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" id="amount" name="amount" class="form-control" required
                            value="{{ old('amount') }}">
                    </div>
                </div>
                <footer class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">Save Bonus</button>
                </footer>
            </form>
        </section>
    </div>

    {{-- Table to Display All Bonuses --}}
    <div class="col-lg-8">
        <section class="card card-admin">
            <header class="card-header">
                <h2 class="card-title">Available Bonus Types</h2>
            </header>
            <div class="card-body">
                @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Amount</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($bonuses as $bonus)
                            <tr>
                                <td>{{ $bonus->name }}</td>
                                <td>{{ number_format($bonus->amount, 2) }}</td>
                                <td class="text-right">
                                    <form action="{{ route('admin.bonuses.destroy', $bonus) }}" method="POST"
                                        style="display:inline-block;"
                                        onsubmit="return confirm('Are you sure you want to delete this bonus?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"><i
                                                class="fas fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">No bonus types have been created yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection