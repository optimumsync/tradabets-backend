

<div class="form-group row">
    {{ Form::label($name, $label, ['class' => 'col-xs-4 col-md-4 control-label text-sm-right pt-2']) }}
    <div class="col-xs-6 col-md-8">
        {{ Form::file($name, array_merge(['class' => 'form-control'], $attributes)) }}
    </div>
    <div class="col-sm-2 col-md-8">
        @if($file_path)
            @canread <a href="{{ asset($file_path) }}" target="_blank" class="btn btn-primary">View</a> @endcanread
        @endif
    </div>
</div>
