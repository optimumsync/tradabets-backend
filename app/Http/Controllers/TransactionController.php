<?php
namespace App\Http\Controllers;
use App\Balance;
use App\Models\Transaction;
use App\Models\InboxNotification;
use App\User;
use App\WithdrawRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\PaymentReport; 
class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
    }
    // -- Helper: Return unique ticket ID for a bet row --
    private function getTicketId($row)
    {
        $req = json_decode($row->request, true);
        $remarks = strtolower($row->remarks ?? '');
        if (strpos($remarks, 'sparket') !== false) {
            return $req['Bet']['betSlipRef'] ?? null;
        } elseif (
            strpos($remarks, 'sportbook') !== false ||
            strpos($remarks, 'sportsbook') !== false ||
            strpos($remarks, 'sportbok') !== false
        ) {
            return $req['transaction_id'] ?? null;
        }
        return null;
    }
    // -- Helper: All bonus credited --
    private function getTotalBonus($userId)
    {
        return Transaction::where([
            ['user_id', $userId],
            ['status', 'bonus']
        ])->sum('amount');
    }
    // -- Helper: Unspent bonus (bonus credited minus bonus spent on bets) --
    private function getUnspentBonus($userId)
    {
        $totalBonus = $this->getTotalBonus($userId);
        $bets = Transaction::where('user_id', $userId)
            ->where('remarks', 'like', '%Bet%') // <-- Use remarks
            ->orderBy('created_at')
            ->get();
        $bonusSpent = 0;
        foreach ($bets as $bet) {
            if ($bonusSpent < $totalBonus) {
                $apply = min($bet->amount, $totalBonus - $bonusSpent);
                $bonusSpent += $apply;
            }
        }
        return max($totalBonus - $bonusSpent, 0);
    }
    // -- Helper: Get tickets placed with bonus funds (array: ticketId => [betRows...]) --
    private function getBonusTickets($userId)
    {
        $totalBonus = $this->getTotalBonus($userId);
        $bets = Transaction::where('user_id', $userId)
            ->where('remarks', 'like', '%Bet%') // <-- Use remarks
            ->orderBy('created_at')
            ->get();
        $bonusSpent = 0;
        $bonusTickets = [];
        foreach ($bets as $bet) {
            if ($bonusSpent < $totalBonus) {
                $apply = min($bet->amount, $totalBonus - $bonusSpent);
                if ($apply > 0) {
                    $ticketId = $this->getTicketId($bet);
                    if ($ticketId) {
                        if (!isset($bonusTickets[$ticketId])) {
                            $bonusTickets[$ticketId] = [];
                        }
                        $bonusTickets[$ticketId][] = $bet;
                    }
                    $bonusSpent += $apply;
                }
            }
        }
        return $bonusTickets;
    }
    // -- Helper: Is a ticket settled? --
    private function isTicketSettled($userId, $ticketId)
    {
        return Transaction::where('user_id', $userId)
            ->where(function ($query) use ($ticketId) {
                $query->where('request', 'like', '%"betSlipRef":"' . $ticketId . '"%')
                    ->orWhere('request', 'like', '%"transaction_id":"' . $ticketId . '"%')
                    ->orWhere('request', 'like', '%'.$ticketId.'%'); // <-- add this line
            })
            ->where(function($query) {
                $query->where('remarks', 'like', '%Win Bet%')
                    ->orWhere('remarks', 'like', '%Loss Bet%');
            })
            ->exists();
    }
    // -- Helper: Is a ticket canceled/voided? --
    private function isTicketCanceled($userId, $ticketId)
    {
        return Transaction::where('user_id', $userId)
            ->where(function ($query) use ($ticketId) {
                $query->where('request', 'like', '%"betSlipRef":"' . $ticketId . '"%')
                    ->orWhere('request', 'like', '%"transaction_id":"' . $ticketId . '"%');
            })
            ->where('remarks', 'like', '%Cancel Bet%')
            ->exists();
    }
    // -- Helper: Does a ticket have minimum odds? (Check all Place Bet bets) --
    private function ticketHasMinOdds($bets, $minOdds = 3.00)
    {
        foreach ($bets as $bet) {
            $req = json_decode($bet->request, true);
            // Try both nested and flat
            $odds = isset($req['Bet']['odds']) ? floatval($req['Bet']['odds']) :
                    (isset($req['odds']) ? floatval($req['odds']) : 0);
            if ($odds < $minOdds) {
                return false;
            }
        }
        return true;
    }
    public function hasUsedFullBonus($userId)
    {
        return $this->getUnspentBonus($userId) == 0;
    }
    // -- Helper: Get eligible tickets for wagering requirement (returns ticketId => [betRows...]) --
    private function getEligibleBonusTickets($userId, $minOdds = 3.00)
    {
        $bonusTickets = $this->getBonusTickets($userId);
        $eligible = [];
        foreach ($bonusTickets as $ticketId => $bets) {
            $settled = $this->isTicketSettled($userId, $ticketId);
            $canceled = $this->isTicketCanceled($userId, $ticketId);
            $minOddsMet = $this->ticketHasMinOdds($bets, $minOdds);

            if ($settled && !$canceled && $minOddsMet) {
                $eligible[$ticketId] = $bets;
            } else {
                \Log::warning('Ticket not eligible', [
                    'user_id' => $userId,
                    'ticket_id' => $ticketId,
                    'settled' => $settled,
                    'canceled' => $canceled,
                    'min_odds_met' => $minOddsMet
                ]);
            }
        }
        return $eligible;
    }
    // -- Helper: Has user met full wagering requirement? --
    private function hasMetWageringRequirement($userId)
    {
        $eligible = $this->getEligibleBonusTickets($userId);
        \Log::info('Eligible tickets for user', [
            'user_id' => $userId,
            'eligible_count' => count($eligible),
            'eligible_ticket_ids' => array_keys($eligible)
        ]);
        if (count($eligible) < 3) {
            \Log::warning('Wagering requirement NOT met', [
                'user_id' => $userId,
                'reason' => 'Less than 3 eligible tickets'
            ]);
        } else {
            \Log::info('Wagering requirement met', ['user_id' => $userId]);
        }
        return count($eligible) >= 3;
    }
    // -- Helper: Calculate eligible bonus winnings (sum Win Bet amounts for eligible tickets) --
    private function getBonusWinnings($userId)
    {
        $eligibleTickets = $this->getEligibleBonusTickets($userId);
        $totalWinnings = 0;
        foreach ($eligibleTickets as $bets) {
            foreach ($bets as $bet) {
                if (stripos($bet->remarks, 'Win Bet') !== false) {
                    $totalWinnings += $bet->amount;
                }
            }
        }
        return $totalWinnings;
    }
    // --- Get cash withdrawable (balance - unspent bonus) ---
    private function getWithdrawableBalance($user)
    {
        $totalBonus = $this->getTotalBonus($user->id);
        // Net winnings = all Win Bet amounts - all Place Bet amounts
        $winAmount = Transaction::where('user_id', $user->id)
            ->where('remarks', 'like', '%Win Bet%')
            ->sum('amount');
        $betAmount = Transaction::where('user_id', $user->id)
            ->where('remarks', 'like', '%Place Bet%')
            ->sum('amount');
        $cashWinnings = max($winAmount - $betAmount, 0);
        // Eligible bonus winnings
        $bonusWinnings = $this->getBonusWinnings($user->id);
        // Withdrawable is the sum of cash winnings and eligible bonus winnings
        return $cashWinnings + $bonusWinnings;
    }
    // --- Transaction History (Unchanged) ---
    public function index(Request $request)
    {
        $user = auth()->user();
        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
        ];
        if ($request->form) {
            $transaction = Transaction::where('user_id', $user->id)
                ->whereBetween('created_at', [$request->form['date_from'], $request->form['date_to']])->get()->all();
        } else {
            $transaction = Transaction::where('user_id', $user->id)
                ->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']])->get()->all();
        }
        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
        $balance = Balance::where('user_id', $user->id)->get()->all();
        $view_data = [
            'transaction' => $transaction,
            'user' => $user,
            'filter_arr' => $filter_arr,
            'balance' => $balance
        ];
        return view('transaction-list.transaction-list-index', $view_data);
    }
    // --- Deposit/Withdraw History (Unchanged) ---
    public function deposit(Request $request)
    {
        $user = auth()->user();
        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
        ];
        if ($request->form) {
            $transaction = Transaction::where([['user_id', $user->id], ['status', 'deposit']])
                ->whereBetween('created_at', [$request->form['date_from'], $request->form['date_to']])->get()->all();
        } else {
            $transaction = Transaction::where([['user_id', $user->id], ['status', 'deposit']])
                ->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']])->get()->all();
        }
        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
        $balance = Balance::where('user_id', $user->id)->get()->all();
        $view_data = [
            'transaction' => $transaction,
            'user' => $user,
            'filter_arr' => $filter_arr,
            'balance' => $balance
        ];
        return view('transaction-list.deposit-index', $view_data);
    }
    public function withdraw(Request $request)
    {
        $user = auth()->user();
        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
        ];
        if ($request->form) {
            $withdraw = WithdrawRequest::where('user_id', $user->id)
                ->whereBetween('created_at', [$request->form['date_from'], $request->form['date_to']])->get()->all();
        } else {
            $withdraw = WithdrawRequest::where('user_id', $user->id)
                ->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']])->get()->all();
        }
        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
        $view_data = [
            'withdraw' => $withdraw,
            'user' => $user,
            'filter_arr' => $filter_arr
        ];
        return view('transaction-list.withdraw-index', $view_data);
    }
    public function depositForm(Request $request)
    {
        $user = auth()->user();
        $request_amount = ($request->amount != null) ? $request->amount : 0.0;
        $avail_balance = Balance::where('user_id', $user->id)->first()->balance;
        $view_data = [
            'avail_balance' => $avail_balance,
            'request_amount' => $request_amount
        ];
        return view('transaction-list.deposit-form', $view_data);
    }
    public function withdrawForm(Request $request)
    {
        $user = auth()->user();
        $avail_balance = $this->getWithdrawableBalance($user);
        $total_balance = Balance::where('user_id', $user->id)->first()->balance;
        $bonus_balance = $this->getUnspentBonus($user->id);

        // Calculate totals
        $total_amount = \App\Models\Transaction::where('user_id', $user->id)->sum('amount');
        $total_bonus = \App\Models\Transaction::where('user_id', $user->id)->where('status', 'bonus')->sum('amount');
        $total_winning = \App\Models\Transaction::where('user_id', $user->id)->where('remarks', 'like', '%Win Bet%')->sum('amount');

        $bet_count = \App\Models\Transaction::where('user_id', $user->id)
            ->where('remarks', 'like', '%Place Bet%')
            ->count();
        $eligibleTickets = $this->getEligibleBonusTickets($user->id);
        $wageringMet = count($eligibleTickets) >= 3;
        $bonusWinnings = $this->getBonusWinnings($user->id);

        $view_data = [
            'avail_balance' => $avail_balance,
            'total_balance' => $total_balance,
            'bonus_balance' => $bonus_balance,
            'bet_count' => $bet_count,
            'wageringMet' => $wageringMet,
            'eligibleTickets' => $eligibleTickets,
            'bonusWinnings' => $bonusWinnings,
            // Pass the new totals
            'total_amount' => $total_amount,
            'total_bonus' => $total_bonus,
            'total_winning' => $total_winning,
        ];
        return view('transaction-list.withdraw-request', $view_data);
    }
    public function withdrawAmount(Request $request)
    {
        $user = auth()->user();
        $avail_balance = $this->getWithdrawableBalance($user);
        // Validation: Not more than withdrawable
        if ($request->withdraw_amount > $avail_balance) {
            return back()->withErrors(['You cannot withdraw bonus funds. Only cash balance and eligible bonus winnings are withdrawable.']);
        }
        // Validation: Wagering requirement
        if (!$this->hasMetWageringRequirement($user->id)) {
            return back()->withErrors(['You must use your bonus on at least 3 different, fully settled tickets, each with minimum odds of 3.00 and no cancel bet, before withdrawing bonus winnings.']);
        }
        $bonusWinnings = $this->getBonusWinnings($user->id);
        if ($request->withdraw_amount > $bonusWinnings + $avail_balance) {
            return back()->withErrors(['You can only withdraw your cash and eligible bonus winnings.']);
        }
        if (!$this->hasUsedFullBonus($user->id)) {
            return back()->withErrors(['You must use your full bonus before withdrawing bonus winnings.']);
        }
        // ACTUAL WITHDRAWAL LOGIC
        $balanceRow = Balance::where('user_id', $user->id)->first();
        $balance_amt = $balanceRow->balance - $request->withdraw_amount;
        $recipient_code = DB::table('user_bank_accounts')
            ->where(['user_id' => $user->id])
            ->where(['Active_status' => "Active"])
            ->pluck('recipient_code');
        WithdrawRequest::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'amount' => $request->withdraw_amount,
            'recipient_code' => $recipient_code[0] ?? null,
        ]);
        Transaction::create([
            'user_id' => $user->id,
            'status' => 'withdraw',
            'amount' => $request->withdraw_amount,
            'opening_balance' => $balanceRow->balance,
            'closing_balance' => $balance_amt
        ]);
        DB::table('balance')
            ->where('user_id', $user->id)
            ->update(['balance' => $balance_amt]);
        session(['avail_balance' => $balance_amt]);
        $super_admin = User::where('role', 'admin')->first()->id;
        InboxNotification::create([
            'receiver' => $super_admin,
            'subject' => 'Regarding Withdraw Request',
            'body' => 'User ' . $user->first_name . ' ' . $user->last_name . ' requested to withdraw the amount.'
        ]);
        return redirect('/withdraw'); // or any success page
    }
    // Admin methods can be unchanged, or you can add bonus logic if needed.
    public function reverseWithdraw(Request $request, WithdrawRequest $withdraw)
    {
        $user=Auth()->user();
        $reverse_withdraw_amount=WithdrawRequest::where([['user_id',$user->id],['id',$withdraw->id]])->first()->amount;
        if($reverse_withdraw_amount!=null) {
            $current_balance = Balance::where('user_id',$user->id)->first()->balance;
            $available_balance = $current_balance + $reverse_withdraw_amount;
            $final_balance = DB::table('balance')
            ->where('user_id', $user->id)
            ->update(['balance' => $available_balance]);
             session([
            'avail_balance' => $available_balance
        ]);
             $form=[];
             $form['status'] = 'reversed';
             $withdraw->update($form);
             Transaction::create(['user_id'=>$user->id,
            'status'=>'reversed',
            'amount'=>$reverse_withdraw_amount,
            'opening_balance'=>$current_balance,
            'closing_balance'=>$available_balance]);
            session()->flash('message-success', 'Amount successfully returned to your wallet.');
                        return redirect('/withdraw');
        }
        else{
            return view('_security.restricted-area.show');
        }
    }
    public function adminTransactionView(Request $request)
    {
        $view_data=[];
        $user=Auth()->user();
        if($user->role=='admin') {
            $filter_arr = [
                'date_from' => date("Y-m-d", strtotime("last week saturday")),
                'date_to' => date("Y-m-d", strtotime("tomorrow")),
                'user' => null,
                'status' => null,
            ];
            if ($request->form) {
                if ($request->form['user'] != null && $request->form['status'] != null) {
                    $users = user::where('first_name', 'like', $request->form['user'] . '%')->get()->pluck('id')->toArray();
                    $transaction = Transaction::where('status', $request->form['status'])->whereIn('user_id', $users)
                        ->whereBetween('created_at', array($request->form['date_from'], $request->form['date_to']))->get()->all();
                } else if ($request->form['user'] != null) {
                    $users = user::where('first_name', 'like', $request->form['user'] . '%')->get()->pluck('id')->toArray();
                    $transaction = Transaction::whereIn('user_id', $users)
                        ->whereBetween('created_at', array($request->form['date_from'], $request->form['date_to']))->get()->all();
                } else if ($request->form['status'] != null) {
                    $transaction = Transaction::where('status', $request->form['status'])
                        ->whereBetween('created_at', array($request->form['date_from'], $request->form['date_to']))->get()->all();
                } else {
                    $transaction = Transaction::whereBetween('created_at', array($request->form['date_from'], $request->form['date_to']))->get()->all();
                }
            } else {
                $transaction = Transaction::whereBetween('created_at', array($filter_arr['date_from'], $filter_arr['date_to']))->get()->all();
            }
            $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
            $users = user::select_list()->all();
            // $balance= Balance::where('user_id',$user->id)->get()->all();
            $view_data = ['transaction' => $transaction, 'users' => $users, 'filter_arr' => $filter_arr];
            return view('admin-views.transaction.admin-transaction-list', $view_data);
        }
        else{
            return view('_security.restricted-area.show');
        }
    }
    public function adminBalanceView(Request $request)
    {
        $user=Auth()->user();
        if($user->role=='admin') {
            $filter_arr = [
                'user' => null,
            ];
            if ($request->form) {
                if ($request->form['user'] != null) {
                    $users = user::where('first_name', 'like', $request->form['user'] . '%')->get()->pluck('id')->toArray();
                    $balance = Balance::WhereIn('user_id', $users)
                        ->get()->all();
                } else {
                    $balance = Balance::all();
                }
            } else {
                $balance = Balance::all();
            }
            $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
            $users = user::select_list()->all();
            // $balance= Balance::where('user_id',$user->id)->get()->all();
            $view_data = ['balance' => $balance, 'users' => $users, 'filter_arr' => $filter_arr];
            return view('admin-views.transaction.admin-balance-list', $view_data);
        }
        else
        {
            return view('_security.restricted-area.show');
        }
    }
    public function withdrawRequestLists(Request $request)
    {
        $user=Auth()->user();
        if($user->role=='admin') {
            $withdraw_requests = WithdrawRequest::where('status', 'pending')->get()->all();
            $view_data = ['withdraw_requests' => $withdraw_requests];
            return view('admin-views.transaction.admin-withdraw-requests-list', $view_data);
        }
        else{
            return view('_security.restricted-area.show');
        }
    }
    public function withdrawRequestListsView(Request $request, WithdrawRequest $withdraw)
    {
        $user=Auth()->user();
        if($user->role=='admin') {
            $view_data=['withdraw'=>$withdraw];
            return view('admin-views.transaction.admin-withdraw-requests-view',$view_data);
        }
        else{
            return view('_security.restricted-area.show');
        }
    }
    public function withdrawRequestListsUpdate(Request $request, WithdrawRequest $withdraw)
    {
        $form=[];
        $user=Auth()->user();
        if($user->role=='admin') {
            $form = $request->form;
            $withdraw->update($form);
            if($form['status']=='rejected')
            {
               $avail_balance= Balance::where('user_id',$withdraw->user_id)->first()->balance;
                if($avail_balance!=null)
                {
                    $balance_amt=$avail_balance+$withdraw->amount;
                    $final_balance = DB::table('balance')
                        ->where('user_id',$withdraw->user_id)
                        ->update(['balance' => $balance_amt]);
                    session([
                        'avail_balance' => $balance_amt
                    ]);
                    \App\Models\Transaction::create(['user_id'=>$withdraw->user_id,
                    'status'=>'rejected',
                    'amount'=>$withdraw->amount,
                    'opening_balance'=>$avail_balance,
                    'closing_balance'=>$balance_amt,
                    'transaction_type' => 'withdraw',
                    'remarks' => 'Withdrawal rejected by admin.']);
                    //var_dump($balance);
                }
            }


            InboxNotification::create([
                'receiver' => $withdraw->user_id,
                'subject' => 'Regarding withdraw status',
                'body' => $withdraw->remarks,
            ]);

            if($form['status']=='approved'){
                // Use $withdraw instead of $withdrawRequest
                $transaction = \App\Models\Transaction::where('user_id', $withdraw->user_id)
                    ->whereRaw('ABS(amount - ?) < 0.01', [$withdraw->amount])
                    ->whereIn('status', ['withdraw', 'Withdraw'])
                    ->latest()
                    ->first();

                if (!$transaction) {
                    \Log::warning('No matching transaction found for withdraw approval', [
                        'user_id' => $withdraw->user_id,
                        'amount' => $withdraw->amount,
                    ]);
                } else {
                    \Log::info('Matching transaction found', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $withdraw->user_id,
                        'amount' => $withdraw->amount,
                        'status' => $transaction->status,
                    ]);
                    $transaction->update([
                        'status' => 'approved',
                        'remarks' => 'Withdrawal approved by admin.',
                    ]);
                    \Log::info('Transaction updated to withdraw_approved', [
                        'transaction_id' => $transaction->id,
                        'user_id' => $withdraw->user_id,
                        'amount' => $withdraw->amount,
                    ]);
                }
            }
            return redirect('/withdraw-requests');
        }
        else{
            return view('_security.restricted-area.show');
        }
    }
    public function withdrawRequestIndividualUpdate(Request $request, $id)
    {
        \Log::info('withdrawRequestIndividualUpdate called', ['id' => $id]);

        // Get the withdraw request info
        $withdrawRequest = DB::table('withdraw_requests')
            ->select('user_id', 'amount')
            ->where('id', $id)
            ->first();

        \Log::info('Fetched withdraw request', (array)$withdrawRequest);

        // Approve the withdraw request
        $updated = DB::table('withdraw_requests')
            ->where('id', $id)
            ->update(['status' => "approved"]);

        \Log::info('Withdraw request status updated', ['id' => $id, 'updated' => $updated]);

        // Update the corresponding transaction status
        if ($withdrawRequest) {
            $transaction = \App\Models\Transaction::where('user_id', $withdrawRequest->user_id)
                ->whereRaw('ABS(amount - ?) < 0.01', [$withdrawRequest->amount])
                ->whereIn('status', ['withdraw', 'Withdraw'])
                ->latest()
                ->first();

            if (!$transaction) {
                \Log::warning('No matching transaction found for withdraw approval', [
                    'user_id' => $withdrawRequest->user_id,
                    'amount' => $withdrawRequest->amount,
                ]);
            } else {
                \Log::info('Matching transaction found', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $withdrawRequest->user_id,
                    'amount' => $withdrawRequest->amount,
                    'status' => $transaction->status,
                ]);
                $transaction->update([
                    'status' => 'withdraw_approved',
                    'remarks' => 'Withdrawal approved by admin.',
                ]);
                \Log::info('Transaction updated to withdraw_approved', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $withdrawRequest->user_id,
                    'amount' => $withdrawRequest->amount,
                ]);
            }
        } else {
            \Log::warning('Withdraw request not found for id', ['id' => $id]);
        }

        return redirect('/withdraw-requests');
    }
    public function withdrawRequestIndividualRejectUpdate(Request $request, $id)
    {
        $update = DB::table('withdraw_requests')
            ->where('id', $id)
            ->update(['status' => "rejected"]);
        $result = DB::table('withdraw_requests')
            ->select('user_id','amount')
            ->where('id', $id)
            ->first();
             $avail_balance= Balance::where('user_id',$result->user_id)->first()->balance;
                if($avail_balance!=null)
                {
                    $balance_amt=$avail_balance+$result->amount;
                    $final_balance = DB::table('balance')
                        ->where('user_id',$result->user_id)
                        ->update(['balance' => $balance_amt]);
                    session([
                        'avail_balance' => $balance_amt
                    ]);
                   $transaction = Transaction::create(['user_id'=>$result->user_id,
                    'status'=>'rejected',
                    'amount'=>$result->amount,
                    'opening_balance'=>$avail_balance,
                    'closing_balance'=>$balance_amt,
                    'transaction_type' => 'withdraw',
                    'remarks' => 'Withdrawal rejected by admin.']);
                }
        return redirect('/withdraw-requests');
    }
    public function withdrawRequestBulkRejectUpdate(Request $request)
    {
        $selected_requests = request('data');
        // $test = DB::table('withdraw_requests')
        //         ->select('amount')
        //         ->where('id', selected_requests[0]);
        return $selected_requests[0];
    }
    public function paystackPaymentReport(Request $request)
    {
        $view_data=[];
        $user=Auth()->user();
        if($user->role=='admin') {
            $filter_arr = [
                'date_from' => date("Y-m-d", strtotime("last week saturday")),
                'date_to' => date("Y-m-d", strtotime("today")),
            ];
            $payment = PaymentReport::all();
            $view_data = ['payment' => $payment, 'filter_arr' => $filter_arr];
            return view('admin-views.transaction.payment-transaction-report', $view_data);
        }
        else{
            return view('_security.restricted-area.show');
        }
    }
}
