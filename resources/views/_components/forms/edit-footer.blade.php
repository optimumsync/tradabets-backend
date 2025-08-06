
    <div class="row">
        <div class="col-md-6">
            <a href="{{ $cancel_url }}" class="btn btn-default">Back</a>
        </div>
        <div class="col-md-6 text-right">
            {{ (isset($update) ? Form::submit('Update', ['class' => 'btn btn-primary']) : '') }}

            @if(isset($btn_arr))
                @foreach($btn_arr as $url => $title)
                    @if(starts_with(strtolower($title), 'add') && !can_user_do_this_action('create'))
                        @continue
                    @endif
                    @if(starts_with(strtolower($title), 'edit') && !can_user_do_this_action('update'))
                        @continue
                    @endif
                    @if(starts_with(strtolower($title), 'delete') && !can_user_do_this_action('delete'))
                        @continue
                    @endif
                    @if(starts_with(strtolower($title), 'undelete') && !can_user_do_this_action('undelete'))
                        @continue
                    @endif

                    @php
                        $a_class = 'btn btn-primary';
                        $a_class = (starts_with(strtolower($title), 'add')) ? 'btn btn-primary' : $a_class;
                        $a_class = (starts_with(strtolower($title), 'edit')) ? 'btn btn-primary' : $a_class;
                        $a_class = (starts_with(strtolower($title), 'delete')) ? 'btn btn-danger is-delete-link' : $a_class;
                        $a_class = (starts_with(strtolower($title), 'done')) ? 'btn btn-success' : $a_class;
                    @endphp

                    <a href="{{ $url }}" class="{{ $a_class }}">{{ $title }}</a>
                @endforeach
            @endif
        </div>
    </div> <!-- /row -->
