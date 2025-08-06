
@component('mail::message')
	<div>
		<h2>Tradabets</h2>
		<h5>User Withdraw Request</h5>
	</div>

	<div class="col-lg-6">
	    <div class="row form-view-row">
	        <div class="col-xs-4 col-md-4 fnt-b">
				<b>User Name:</b> {{ $data['name'] }}<br>
				<b>User ID:</b> {{ $data['user_id'] }}<br>
				<b>User Email:</b> {{ $data['email'] }}<br>
				<b>Amount:</b> {{ $data['amount'] }}<br>
				<b>Status:</b> {{ $data['status'] }}<br>
				<b>Request ID:</b> {{ $data['request_id'] }}<br>
				<b>Requested On:</b> {{ $data['requested_On'] }}<br>
			</div>
	    </div>
	</div>

<p>Click the below button to Approve/Reject this request in application</p> 
@component('mail::button', ['url' => $data['url']])
View In App
@endcomponent

<br><br>
Regards,<br>
<b>{{ config('app.name') }}</b>
