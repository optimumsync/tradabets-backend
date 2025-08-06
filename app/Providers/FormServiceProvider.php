<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Form;

class FormServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // text
        Form::component('text_md12', '_components.form-elements.text-md12', ['label' => null, 'name' => null, 'value' => null, 'attributes' => []]);
        Form::component('text_md6', '_components.form-elements.text-md6', ['label' => null, 'name' => null, 'value' => null, 'attributes' => []]);

        // textarea
        Form::component('textarea_md12', '_components.form-elements.textarea-md12', ['label' => null, 'name' => null, 'value' => null, 'attributes' => ['rows' => 3]]);
        Form::component('textarea_md6', '_components.form-elements.textarea-md6', ['label' => null, 'name' => null, 'value' => null, 'attributes' => ['rows' => 3]]);

        // password
        Form::component('password_md12', '_components.form-elements.password-md12', ['label' => null, 'name' => null, 'attributes' => [], 'append' => '']);
        Form::component('password_md6', '_components.form-elements.password-md6', ['label' => null, 'name' => null, 'attributes' => [], 'append' => '']);

        // select
        Form::component('select_md12', '_components.form-elements.select-md12', ['label' => null, 'name' => null, 'options' => null, 'value' => null, 'attributes' => [], 'append' => '']);
        Form::component('select_md6', '_components.form-elements.select-md6', ['label' => null, 'name' => null, 'options' => null, 'value' => null, 'attributes' => [], 'append' => '']);

        // status select
        Form::component('status_select_md12', '_components.form-elements.status-select-md12', ['label' => null, 'name' => null, 'value' => null, 'attributes' => [], 'append' => '']);
        Form::component('status_select_md6', '_components.form-elements.status-select-md6', ['label' => null, 'name' => null, 'value' => null, 'attributes' => [], 'append' => '']);
        Form::component('status_master_select_md12', '_components.form-elements.status-master-select-md12', ['label' => null, 'name' => null, 'value' => null, 'attributes' => [], 'append' => '']);

        // file
        Form::component('file_md12', '_components.form-elements.file-md12', ['label' => null, 'name' => null, 'attributes' => [], 'file_path' => null, 'append' => '']);
        Form::component('file_md6', '_components.form-elements.file-md6', ['label' => null, 'name' => null, 'attributes' => [], 'file_path' => null, 'append' => '']);

        // select
        Form::component('tag_select_md12', '_components.form-elements.tag-select-md12', ['label' => null, 'name' => null, 'options' => null, 'value' => null, 'attributes' => [], 'append' => '']);
        Form::component('tag_select_md6', '_components.form-elements.tag-select-md6', ['label' => null, 'name' => null, 'options' => null, 'value' => null, 'attributes' => [], 'append' => '']);
    }
}
