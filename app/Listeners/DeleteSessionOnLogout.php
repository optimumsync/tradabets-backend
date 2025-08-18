<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeleteSessionOnLogout
{
    /**
     * We no longer need the request object, so the constructor can be removed or left empty.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        // The Logout event contains the user who is logging out.
        // We will delete all sessions belonging to this user.
        if ($event->user) {
            DB::table('sessions')->where('user_id', $event->user->id)->delete();
            
            // Optional: You can keep this log for confirmation.
            Log::info('Deleted all database sessions for user ID: ' . $event->user->id);
        }
    }
}