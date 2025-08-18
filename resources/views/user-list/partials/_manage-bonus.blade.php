<div class="pt-3">
    <section class="card card-modern">
        <div class="card-header">
            <h2 class="card-title">Award a New Bonus</h2>
        </div>
        <form action="{{ route('admin.user-bonuses.award') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            <div class="card-body">
                @if($bonuses->isEmpty())
                <div class="alert alert-warning">
                    There are no bonuses available. Please <a href="{{ route('admin.bonuses.index') }}"
                        class="alert-link">manage bonus types</a> first.
                </div>
                @else
                <div class="form-group row">
                    <label for="bonus_id" class="col-sm-4 col-lg-3 text-sm-right control-label">Select Bonus <span
                            class="text-danger">*</span></label>
                    <div class="col-sm-8 col-lg-7">
                        <select id="bonus_id" name="bonus_id" class="form-control" required>
                            <option value="">-- Choose a Bonus --</option>
                            @foreach($bonuses as $bonus)
                            <option value="{{ $bonus->id }}">
                                {{ $bonus->name }} (Amount: {{ number_format($bonus->amount, 2) }})
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif
            </div>
            <footer class="card-footer text-right">
                <button type="submit" class="btn btn-primary" {{ $bonuses->isEmpty() ? 'disabled' : '' }}>
                    <i class="fas fa-gift mr-2"></i> Award Bonus
                </button>
            </footer>
        </form>
    </section>
</div>