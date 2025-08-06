

<div class="form-group row">
    {{ Form::label($name, $label, ['class' => 'col-xs-4 col-md-2 control-label text-sm-right pt-2']) }}
    <div class="col-sm-8 col-md-8">
        {{ Form::select($name, array_combine($options, $options), $value, array_merge([
                'class' => 'form-control has-content-type-tags',
                'multiple' => 'multiple',
                'data-tag-class' => 'badge badge-primary'
            ], $attributes)) }}
    </div>
</div>
