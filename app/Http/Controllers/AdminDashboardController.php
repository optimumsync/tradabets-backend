<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(Request $request)
    {
        // --- 1. Set Date Range for Filtering ---
        // Validate incoming dates, or set defaults
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Use selected dates or default to the last 30 days
        $startDate = $request->filled('start_date') ? Carbon::parse($request->start_date) : Carbon::now()->subDays(29);
        $endDate = $request->filled('end_date') ? Carbon::parse($request->end_date) : Carbon::now();

        // --- 2. Data for Daily Registration Line Graph (for the selected period) ---
        $dailyRegistrations = User::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count')
            )
            ->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Prepare data for Chart.js
        $registrationLabels = $dailyRegistrations->pluck('date')->map(function ($date) {
            return Carbon::parse($date)->format('Y-m-d');
        });
        $registrationData = $dailyRegistrations->pluck('count');

        // --- 3. Data for Player Count Stats (for the selected period) ---
        $todayCount = User::whereDate('created_at', Carbon::today())->count(); // Today's count is independent of the filter
        $thisWeekCount = User::whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count();
        $thisMonthCount = User::whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count();

        // --- 4. Pass all data to the view ---
        return view('admin-views.transaction.dashboard', [
            'registrationLabels' => $registrationLabels,
            'registrationData' => $registrationData,
            'todayCount' => $todayCount,
            'thisWeekCount' => $thisWeekCount,
            'thisMonthCount' => $thisMonthCount,
            'filterStartDate' => $startDate->format('Y-m-d'),
            'filterEndDate' => $endDate->format('Y-m-d'),
        ]);
    }
}