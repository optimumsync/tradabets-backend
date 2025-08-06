<?php

namespace App\Http\Controllers;

use App\Balance;
use App\Models\InboxNotification;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\User;

class BetListController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }

    public function index(Request $request)
    {
        $user = auth()->user();

        // Extract date filters from the 'form' array input
        $formInput = $request->input('form', []);

        $filter_arr = array_merge([
            'date_from' => date("Y-m-d", strtotime("-365 days")),
            'date_to' => date("Y-m-d", strtotime("+365 days")),
        ], $formInput);

        $dateFrom = $filter_arr['date_from'];
        $dateTo = $filter_arr['date_to'];

        $query = Transaction::query();

        if ($user->role === 'admin') {
            $users = User::select('id', 'first_name', 'last_name', 'email')->get();

            if ($request->filled('user_id')) {
                $filter_arr['user_id'] = $request->input('user_id');
                $query->where('user_id', $filter_arr['user_id']);
            }
        } else {
            $users = [];
            $query->where('user_id', $user->id);
        }

        $query->whereBetween('created_at', [$dateFrom, $dateTo])
              ->where(function ($q) {
                  $q->where('remarks', 'like', '%Place Bet%')
                    ->orWhere('remarks', 'like', '%Win Bet%')
                    ->orWhere('remarks', 'like', '%Loss Bet%');
              })
              ->orderBy('created_at', 'desc');

        $transactions = $query->get();

        // Build lookup maps
        $sparketPlaceBets = [];
        $sportsbookPlaceBets = [];

        foreach ($transactions as $row) {
            $req = json_decode($row->request, true);
            $remarks = strtolower($row->remarks ?? '');

            if (strpos($remarks, 'sparket') !== false && strpos($remarks, 'place bet') !== false) {
                $betSlipRef = $req['Bet']['betSlipRef'] ?? null;
                if ($betSlipRef) {
                    $sparketPlaceBets[$betSlipRef] = $row->created_at;
                }
            }

            if ((strpos($remarks, 'sportbook') !== false ||
                 strpos($remarks, 'sportsbook') !== false ||
                 strpos($remarks, 'sportbok') !== false) &&
                strpos($remarks, 'place bet') !== false) {
                $transactionId = $req['transaction_id'] ?? null;
                if ($transactionId) {
                    $sportsbookPlaceBets[$transactionId] = $row->created_at;
                }
            }
        }

        $betList = [];

        foreach ($transactions as $row) {
            $req = json_decode($row->request, true);
            $amount = $row->amount;
            $remarks = strtolower($row->remarks ?? '');

            $outcome = 'Placed Bet';
            if (strpos($remarks, 'win') !== false) {
                $outcome = 'Win';
            } elseif (strpos($remarks, 'loss') !== false) {
                $outcome = 'Lose';
            }

            if (strpos($remarks, 'sparket') !== false) {
                $betType = 'sparket';
            } elseif (strpos($remarks, 'sportbook') !== false ||
                      strpos($remarks, 'sportsbook') !== false ||
                      strpos($remarks, 'sportbok') !== false) {
                $betType = 'sportsbook';
            } else {
                $betType = 'unknown';
            }

            $betSlipRef = '';
            $betDate = $row->created_at;
            $resultDate = '';

            if ($betType === 'sparket') {
                $betSlipRef = $req['Bet']['betSlipRef'] ?? '';
                if ($betSlipRef && isset($sparketPlaceBets[$betSlipRef])) {
                    $betDate = $sparketPlaceBets[$betSlipRef];
                }
                if (strpos($remarks, 'win') !== false) {
                    $resultDate = $row->created_at;
                }
            } elseif ($betType === 'sportsbook') {
                $betSlipRef = $req['transaction_id'] ?? '';
                if ($betSlipRef && isset($sportsbookPlaceBets[$betSlipRef])) {
                    $betDate = $sportsbookPlaceBets[$betSlipRef];
                }
                if (strpos($remarks, 'win') !== false) {
                    $resultDate = $row->created_at;
                }
            }

            $winning = strpos($remarks, 'win') !== false ? $amount : '';

            $betList[] = [
                'bet_slip' => $betSlipRef,
                'bet_type' => $betType,
                'bet_date' => $betDate,
                'result_date' => $resultDate,
                'amount' => $amount,
                'outcome' => $outcome,
                'winning' => $winning,
                'settled_bets' => $row->settled_bets ?? '',
                'user_id' => $row->user_id,
            ];
        }

        $balance = ($user->role !== 'admin') ? Balance::where('user_id', $user->id)->get() : null;

        return view('bet-list.bet-list-index', [
            'betList' => $betList,
            'user' => $user,
            'filter_arr' => $filter_arr,
            'balance' => $balance,
            'users' => $users,
        ]);
    }

    public function betListCashout(Request $request)
    {
        $user = auth()->user();

        $formInput = $request->input('form', []);
        $filter_arr = array_merge([
            'date_from' => date("Y-m-d", strtotime("-365 days")),
            'date_to' => date("Y-m-d", strtotime("+365 days")),
        ], $formInput);

        $dateFrom = $filter_arr['date_from'];
        $dateTo = $filter_arr['date_to'];

        $query = Transaction::query();

        if ($user->role === 'admin') {
            $users = User::select('id', 'first_name', 'last_name', 'email')->get();

            if ($request->filled('user_id')) {
                $filter_arr['user_id'] = $request->input('user_id');
                $query->where('user_id', $filter_arr['user_id']);
            }
        } else {
            $users = [];
            $query->where('user_id', $user->id);
        }

        $query->whereBetween('created_at', [$dateFrom, $dateTo])
              ->where('remarks', 'like', '%Win Bet%')
              ->orderBy('created_at', 'desc');

        $transactions = $query->get();

        $sparketPlaceBets = [];
        $sportsbookPlaceBets = [];

        foreach ($transactions as $row) {
            $req = json_decode($row->request, true);
            $remarks = strtolower($row->remarks ?? '');

            if (strpos($remarks, 'sparket') !== false && strpos($remarks, 'place bet') !== false) {
                $betSlipRef = $req['Bet']['betSlipRef'] ?? null;
                if ($betSlipRef) {
                    $sparketPlaceBets[$betSlipRef] = $row->created_at;
                }
            }

            if ((strpos($remarks, 'sportbook') !== false ||
                 strpos($remarks, 'sportsbook') !== false ||
                 strpos($remarks, 'sportbok') !== false) &&
                strpos($remarks, 'place bet') !== false) {
                $transactionId = $req['transaction_id'] ?? null;
                if ($transactionId) {
                    $sportsbookPlaceBets[$transactionId] = $row->created_at;
                }
            }
        }

        $betList = [];

        foreach ($transactions as $row) {
            $req = json_decode($row->request, true);
            $amount = $row->amount;
            $remarks = strtolower($row->remarks ?? '');

            if (strpos($remarks, 'sparket') !== false) {
                $betType = 'sparket';
            } elseif (strpos($remarks, 'sportbook') !== false ||
                      strpos($remarks, 'sportsbook') !== false ||
                      strpos($remarks, 'sportbok') !== false) {
                $betType = 'sportsbook';
            } else {
                $betType = 'unknown';
            }

            $betSlipRef = '';
            $betDate = $row->created_at;

            if ($betType === 'sparket') {
                $betSlipRef = $req['Bet']['betSlipRef'] ?? '';
                $betDate = $sparketPlaceBets[$betSlipRef] ?? $betDate;
            } elseif ($betType === 'sportsbook') {
                $betSlipRef = $req['transaction_id'] ?? '';
                $betDate = $sportsbookPlaceBets[$betSlipRef] ?? $betDate;
            }

            $betList[] = [
                'coupon_id' => $betSlipRef,
                'bet_type' => $betType,
                'bet_date' => $betDate,
                'amount' => $amount,
                'pot_win' => $amount,
                'outcome' => 'Win',
                'user_id' => $row->user_id,
            ];
        }

        $balance = ($user->role !== 'admin') ? Balance::where('user_id', $user->id)->get() : null;

        return view('bet-list.bet-list-cashout-index', [
            'betList' => $betList,
            'user' => $user,
            'filter_arr' => $filter_arr,
            'balance' => $balance,
            'users' => $users ?? null,
        ]);
    }
}
