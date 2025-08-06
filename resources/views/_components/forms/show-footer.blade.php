

    <div class="row">
        <div class="col-md-6">
            @if(isset($back_url))
                <a href="{{ $back_url }}" class="btn btn-default">Back</a>
            @endif
        </div>
        <div class="col-md-6 text-right">
            @if(isset($prepend_btn_arr))
                @foreach($prepend_btn_arr as $btn)
                    {!! $btn  !!}
                @endforeach
            @endif

            @if(isset($edit_url) && $edit_url && can_user_do_this_action('update'))
                <a href="{{ $edit_url }}" class="btn btn-primary" id="btnEdit">Edit</a>
            @endif
            @if(isset($delete_url) && $delete_url && can_user_do_this_action('delete'))
                <a href="{{ $delete_url }}" class="btn btn-danger is-delete-link">Delete</a>
            @endif

            @if(isset($append_btn_arr))
                @foreach($append_btn_arr as $btn)
                    {!! $btn  !!}
                @endforeach
            @endif
        </div>
    </div> <!-- /row -->
