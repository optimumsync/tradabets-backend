<?php

namespace App\Helpers;

use App\Models\InboxNotification;

class InboxNotificationHelper
{
	/**
     * Uodates the inbox unread session int.
     *
     * @return void
     */
	public static function update_inbox_notification_sessions()
	{
        // get
        $user=auth()->user();
		$inbox_notifications =  InboxNotification::where('read_at', null)->whereIn('receiver',array($user->id,0))->get()->sortByDesc('created_at');//InboxNotification::where('read_at', null)/*->whereIn('receiver',array($user->id,0))*/->get()->all();

		// set session
		session([
			'inbox_notifications' => ($inbox_notifications)?$inbox_notifications->take(5):null,
			'num_inbox_notifications' => ($inbox_notifications)?$inbox_notifications->count():0
		]);
	}
}
