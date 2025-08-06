

<div class="form-group row">
    {{ Form::label($name, $label, ['class' => 'col-xs-4 col-md-2 control-label text-sm-right pt-2']) }}
    <div class="col-sm-8 col-md-4">
        {{ Form::select($name, config('custom-form.status-arr'), $value, array_merge(['class' => 'form-control'], $attributes)) }}
    </div>
</div>
