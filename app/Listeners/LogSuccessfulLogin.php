<?php

namespace App\Listeners;

// MODIFIED: Import the Login event
use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\LoginHistory;
use Illuminate\Http\Request;

class LogSuccessfulLogin
{
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  Login  $event  <-- MODIFIED
     * @return void
     */
    public function handle(Login $event) // <-- MODIFIED
    {
        if ($event->user) {
            LoginHistory::create([
                'user_id' => $event->user->id,
                'ip_address' => $this->request->ip(),
                'user_agent' => $this->request->header('User-Agent'),
                'login_at' => now(),
            ]);
        }
    }
}