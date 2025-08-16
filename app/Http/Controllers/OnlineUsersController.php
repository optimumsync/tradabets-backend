<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use Carbon\Carbon;

class OnlineUsersController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a list of currently online users.
     */
    public function index()
    {
        // Get the session lifetime in minutes from your session config file.
        $minutes = config('session.lifetime', 120);
        $time = Carbon::now()->subMinutes($minutes);

        // Query the sessions table for recent activity.
        $sessions = DB::table('sessions')
            ->where('last_activity', '>', $time->getTimestamp())
            ->whereNotNull('user_id')
            ->get();

        // Get the unique user IDs from the active sessions.
        $userIds = $sessions->pluck('user_id')->unique();

        // Fetch the user models for the online users.
        $onlineUsers = User::whereIn('id', $userIds)->get();

        // Pass the data to the view.
        return view('admin.online-users', [
            'onlineUsers' => $onlineUsers,
        ]);
    }
}