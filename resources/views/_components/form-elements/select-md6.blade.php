

<div class="form-group row">
    {{ Form::label($name, $label, ['class' => 'col-xs-4 col-md-4 control-label text-sm-right pt-2']) }}
    <div class="col-xs-8 col-md-8">
        {{ Form::select($name, $options, $value, array_merge(['class' => 'form-control'], $attributes)) }}
    </div>
</div>
