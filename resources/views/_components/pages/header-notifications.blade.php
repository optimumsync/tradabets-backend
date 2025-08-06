

	@php
		$num_inbox_notifications = Session::get('num_inbox_notifications');
	@endphp

	<ul class="notifications mobile-admin-notifications">
		<li>
			<a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
				<i class="fas fa-envelope"></i>
				@if($num_inbox_notifications)
					<span class="badge">{{ $num_inbox_notifications }}</span>
				@endif
			</a>

			<div class="dropdown-menu notification-menu">
				<div class="notification-title">
					@if($num_inbox_notifications)
						<span class="float-right badge badge-default">{{ $num_inbox_notifications }}</span>
					@endif
					Messages
				</div>

				<div class="content">
					<ul>
                        @if($num_inbox_notifications)
						@foreach(session('inbox_notifications', []) as $row)
						<li>
							<a href="/inbox/message-view/{{ $row->inbox_notification_id }}" class="clearfix">
								<span class="title">{{ $row->subject  }}</span>
							</a>
						</li>
						@endforeach
                            @endif
					</ul>

					<hr />

					<div class="text-right">
						<a href="/inbox" class="view-more">View All</a>
					</div>
				</div>
			</div>
		</li>
		<li>
			@php
				$num_shared_user_actions = session('num_shared_user_actions', '0');
			@endphp

			<a href="#" class="dropdown-toggle notification-icon" data-toggle="dropdown">
				<i class="fas fa-bell"></i>
				@if($num_shared_user_actions)
					<span class="badge">{{ $num_shared_user_actions }}</span>
				@endif
			</a>

			<div class="dropdown-menu notification-menu">
				<div class="notification-title">
					@if($num_shared_user_actions)
						<span class="float-right badge badge-default">{{ $num_shared_user_actions }}</span>
					@endif
					Alerts
				</div>

				<div class="content">
					<ul>
						@foreach(session('shared_user_actions', []) as $row)
						<li>
							<a href="{{ ($row->inbox_notification) ? '/inbox/'.$row->inbox_notification->hash_id : '#' }}" class="clearfix">
								<div class="image">
									<i class="fas fa-thumbs-up bg-info text-light"></i>
								</div>

							</a>
						</li>
						@endforeach
					</ul>

						<hr />

					<div class="text-right">
						<a href="/inbox" class="view-more">View All</a>
					</div>
				</div>
			</div>
		</li>
	</ul>
