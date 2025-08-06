<div class="row align-items-center">
	<div class="col-xs-6 col-sm-4 col-md-4 col-lg-2">
		<div class="h7 m-0 pl-0">Actions:</div>
	</div>
	<div class="col-sm-4 col-md-4 col-lg-1">

		<div class="btn-group flex-wrap">
            <?php
                if(\Request::route()->getName() == 'deposits') {
                    $label = 'Quick Deposit';
                } elseif(\Request::route()->getName() == 'withdraw') {
                    $label = 'Quick Withdraw';
                }
                else {
                    $label = 'Select An Action...';
                }
            ?>
			<button type="button" class="mb-1 mt-1 mr-1 btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">{{$label}} <span class="caret"></span></button>
			<div class="dropdown-menu" role="menu">

				@foreach($btn_link_arr as $url => $title)
					@if(str_contains($url, '/create') && !can_user_do_this_action('create'))
						@continue
					@endif

					@php
						$a_class = 'dropdown-item';
						$a_class .= (isset($active_url) && $active_url == $url) ? ' active' : '';
					@endphp

					<a class="{{ $a_class  }}" href="{{ $url }}">{{ $title }}</a>
				@endforeach

			</div>
		</div>

	</div>
</div> <!-- /row -->
