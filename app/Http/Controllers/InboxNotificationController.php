<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;

use App\Models\InboxNotification;

use App\Helpers\InboxNotificationHelper;

use Illuminate\Http\Request;

class InboxNotificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // data
        $view_data = [
        		'active_url' => '/'.basename($_SERVER['REQUEST_URI']),
			];

        // get
        $user=auth()->user();
        $view_data['inbox_notificationrs'] = InboxNotification::whereIn('receiver',array($user->id,0))
			->get()->sortByDesc('created_at');


        // view
        return view('inbox-notifications.index', $view_data);
    }

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return void
	 */
    public function create()
    {

    }

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return void
	 */
    public function store(Request $request)
    {

    }

	/**
	 * Display the specified resource.
	 *
	 * @param InboxNotification $inbox_notification
	 * @return \Illuminate\Http\Response
	 */
    public function show(InboxNotification $inbox_notification)
    {
        // update
        $inbox_notification = $inbox_notification->was_read($inbox_notification);

        // data
        $view_data = ['inbox_notification' => $inbox_notification];

        // view
        return view('inbox-notifications.', $view_data);
    }

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param Request $request
	 * @param InboxNotification $inbox_notification
	 * @return void
	 */
    public function edit(Request $request, InboxNotification $inbox_notification)
    {
		//
    }

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param InboxNotification $inbox_notification
	 * @return \Illuminate\Http\Response
	 */
    public function update(Request $request, InboxNotification $inbox_notification)
    {
        //
    }

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param InboxNotification $inbox_notification
	 * @return \Illuminate\Http\Response
	 * @throws \Exception
	 */
    public function destroy(InboxNotification $inbox_notification)
    {
        // delete
        $inbox_notification->delete();

        // msg
        session()->flash('message-success', 'The inbox notification was successfully deleted.');

        // redirect
        return redirect('/inbox');
    }

	/**
	 * Mark all unread messages as read.* @param InboxNotification $inbox_notification
	 *
	 * @return \Illuminate\Http\Response
	 * @throws \Exception
	 */
    public function mark_all_as_read(InboxNotification $notification)
    {
    	// update
		/*InboxNotification::owner()->where('read_at', null)->update([
			'read_at' => now()
		]);*/
        $form=['read_at'=>now()];
        $notification->update($form);



		// update
		InboxNotificationHelper::update_inbox_notification_sessions();


		// msg
		//session()->flash('message-success', 'The inbox notifications were all successfully marked as read.');
    $view_data=['notification'=>$notification];
		// redirect
		return view('inbox-notifications.notification-view',$view_data);
    }

}
