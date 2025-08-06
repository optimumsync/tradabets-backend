<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Use Auth facade for consistency
use Illuminate\Support\Facades\DB;
use App\Balance;
use App\UserBankDetails;
use App\Models\InboxNotification;
use App\Models\Transaction; // Use full namespace if Transaction model is in App\Models
use App\KycDocument;
use App\User; // Ensure this is the correct path to your User model
use App\WithdrawRequest;
use App\PaymentReport;

class TransactionApiController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // This is the ONLY line you need to change in the controller itself.
        // It applies the 'jwt.auth' middleware to all methods in this controller.
        // Ensure 'jwt.auth' is correctly aliased to Tymon\JWTAuth\Http\Middleware\Authenticate::class in Kernel.php.
        $this->middleware('jwt.auth');
    }

    /**
     * Get all transactions for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Step 1: Dump the raw request body
        \Log::info('Raw Request Body', [$request->getContent()]);

        // Step 2: Dump the input values
        $date_from = $request->input('form.date_from');
        $date_to = $request->input('form.date_to');
        \Log::info('Received Filter Dates', [
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);

        // Step 3: Use defaults if values are not set
        $default_date_from = date("Y-m-d", strtotime("last week saturday"));
        $default_date_to = date("Y-m-d", strtotime("tomorrow"));

        $date_from = $date_from ?? $default_date_from;
        $date_to = $date_to ?? $default_date_to;

        // Step 4: Log final date range
        \Log::info('Final Filter Dates', [
            'date_from' => $date_from,
            'date_to' => $date_to
        ]);

        // Step 5: Fetch transactions
        $transactions = Transaction::where('user_id', $user->id)
            ->whereBetween('created_at', [$date_from, $date_to])
            ->orderBy('created_at', 'desc')
            ->get();

        \Log::info('Transaction Count', ['count' => $transactions->count()]);

        $balance = Balance::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
                'balance' => $balance,
                'filter' => [
                    'date_from' => $date_from,
                    'date_to' => $date_to,
                ]
            ]
        ]);
    }

    /**
     * Get deposit transactions for the authenticated user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deposit(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT

        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
        ];

        if (isset($request->form['date_from']) && isset($request->form['date_to'])) {
            $transactions = Transaction::where([['user_id', $user->id], ['status', 'deposit']])
                ->whereBetween('created_at', [$request->form['date_from'], $request->form['date_to']])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $transactions = Transaction::where([['user_id', $user->id], ['status', 'deposit']])
            ->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']])
            ->orderBy('created_at', 'desc')
            ->get();
        }

        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
        $balance = Balance::where('user_id', $user->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
                'balance' => $balance,
                'filter' => $filter_arr
            ]
        ]);
    }

    /**
     * Get withdraw transactions for the authenticated user, or for a specific user if admin.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdraw(Request $request)
    {
        $authUser = Auth::user(); // Authenticated user via JWT
        $requestedUserId = $request->input('user_id');

        // Determine the target user based on request and authorization
        if (!$requestedUserId) {
            // If no user_id is provided, assume the authenticated user wants their own data
            $targetUser = $authUser;
        } else {
            // Find the requested user
            $targetUser = User::findOrFail($requestedUserId);

            // Authorization check: Only allow access if it's the user themselves or an admin
            // Assuming 'is_admin' is a property or method on your User model
            if ($authUser->id !== $targetUser->id && !($authUser->is_admin ?? false)) { // Added null coalesce for safety
                return response()->json(['message' => 'Access denied. You are not authorized to access this user\'s withdrawals.'], 403);
            }
        }

        // Filter dates
        $defaultFilter = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
        ];

        $requestForm = $request->form ?? [];
        $dateFrom = $requestForm['date_from'] ?? $defaultFilter['date_from'];
        $dateTo = $requestForm['date_to'] ?? $defaultFilter['date_to'];

        // Fetch withdrawals for the target user
        $withdraws = WithdrawRequest::where('user_id', $targetUser->id)
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get();

        $response = [
            'success' => true,
            'transaction_type' => 'withdraw',
            'data' => [
                'withdraws' => $withdraws,
                'filter' => array_merge($defaultFilter, $requestForm),
            ]
        ];

        // If an admin is accessing another user's data, include that info in the response
        if ($authUser->id !== $targetUser->id && ($authUser->is_admin ?? false)) {
            $response['data']['user'] = [
                'id' => $targetUser->id,
                'name' => $targetUser->first_name . ' ' . $targetUser->last_name, // Assuming first_name, last_name
                'email' => $targetUser->email
            ];
        }

        return response()->json($response);
    }

    /**
     * Get deposit form data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function depositForm(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT
        $request_amount = $request->amount ?? 0.0; // Use null coalesce for default value

        $balance = Balance::where('user_id', $user->id)->first();
        $avail_balance = $balance ? $balance->balance : 0; // Handle case where balance might not exist

        return response()->json([
            'success' => true,
            'data' => [
                'avail_balance' => $avail_balance,
                'request_amount' => $request_amount
            ]
        ]);
    }

    /**
     * Get withdraw form data.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawForm(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT

        $total_balance = Balance::where('user_id', $user->id)->first();
        $avail_balance = $total_balance ? $total_balance->balance : 0; // Handle case where balance might not exist

        $check_bonus = Transaction::where(['user_id' => $user->id, 'status' => 'bonus'])->first();
        $bonus = ($check_bonus) ? $check_bonus->amount : 0; // Bonus might be needed for logic, but not returned.

        $bet_count = Transaction::where('user_id', $user->id)->where('remarks', 'like', '%Place Bet%')->count();

        return response()->json([
            'success' => true,
            'transaction_type' => 'withdraw',
            'data' => [
                'avail_balance' => $avail_balance,
                'bet_count' => $bet_count
            ]
        ]);
    }

    /**
     * Reverse a withdraw.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\WithdrawRequest $withdraw Implicitly bound withdraw request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function reverseWithdraw(Request $request, WithdrawRequest $withdraw)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Ensure the authenticated user is the owner of the withdraw request
        if ($withdraw->user_id !== $user->id) {
             return response()->json([
                'success' => false,
                'message' => 'Unauthorized action. This withdrawal request does not belong to you.'
            ], 403);
        }

        // Ensure the withdraw request is still reversible (e.g., pending)
        if ($withdraw->status !== 'pending') { // Assuming only 'pending' can be reversed
             return response()->json([
                'success' => false,
                'message' => 'This withdrawal request cannot be reversed.'
            ], 400);
        }

        $reverse_withdraw_amount = $withdraw->amount;

        DB::beginTransaction(); // Start a database transaction for atomicity
        try {
            $balance = Balance::where('user_id', $user->id)->first();
            if (!$balance) {
                throw new \Exception('User balance record not found.');
            }
            $current_balance = $balance->balance;
            $available_balance = $current_balance + $reverse_withdraw_amount;

            $balance->update(['balance' => $available_balance]);

            $withdraw->update([
                'status' => 'reversed',
                'remarks' => $request->remarks ?? 'Withdrawal reversed by user.' // Add a remark if needed
            ]);

            Transaction::create([
                'user_id' => $user->id,
                'status' => 'reversed',
                'amount' => $reverse_withdraw_amount,
                'opening_balance' => $current_balance,
                'closing_balance' => $available_balance,
                'remarks' => 'Withdrawal reversed by user', // Add a remark
                'transaction_type' => 'Reverse withdraw'
            ]);

            DB::commit(); // Commit the transaction

            return response()->json([
                'success' => true,
                'message' => 'Amount successfully returned to your wallet.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            // Log the error for debugging
            \Log::error('Error reversing withdrawal for user ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reversing the withdrawal. Please try again.'
            ], 500);
        }
    }

    /**
     * Request withdraw amount.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawAmount(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Basic validation for withdraw_amount
        $validator = \Validator::make($request->all(), [
            'withdraw_amount' => ['required', 'numeric', 'min:1'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $balance = Balance::where('user_id', $user->id)->first();
        if (!$balance) {
            return response()->json([
                'success' => false,
                'message' => 'User balance not found.'
            ], 404);
        }

        $avail_balance = $balance->balance;
        $requested_withdraw_amount = $request->withdraw_amount;

        if ($requested_withdraw_amount > $avail_balance) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient balance for withdrawal.'
            ], 400);
        }

        $balance_amt_after_withdraw = $avail_balance - $requested_withdraw_amount;

        // Check for active recipient code (bank account)
        $recipient_code = DB::table('user_bank_accounts') // Assuming this table exists
            ->where('user_id', $user->id)
            ->where('Active_status', 'Active') // Case-sensitive 'Active'
            ->pluck('recipient_code')
            ->first(); // Get the first recipient code

        if (empty($recipient_code)) { // Check if empty (null or empty string)
            return response()->json([
                'success' => false,
                'message' => 'No active bank account found for withdrawal. Please add one.'
            ], 400);
        }

        DB::beginTransaction(); // Start transaction
        try {
            // Create withdraw request
            WithdrawRequest::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'amount' => $requested_withdraw_amount,
                'recipient_code' => $recipient_code,
            ]);

            // Create transaction record for withdrawal
            Transaction::create([
                'user_id' => $user->id,
                'status' => 'withdraw',
                'amount' => $requested_withdraw_amount,
                'opening_balance' => $avail_balance,
                'closing_balance' => $balance_amt_after_withdraw,
                'remarks' => 'Withdrawal request',
                'transaction_type' => 'withdraw'
            ]);

            // Update user balance
            $balance->update(['balance' => $balance_amt_after_withdraw]);

            DB::commit(); // Commit the transaction

            // Notify admin
            $super_admin = User::where('role', 'admin')->first(); // Assumes 'role' column on User model
            if ($super_admin) {
                InboxNotification::create([
                    'receiver' => $super_admin->id,
                    'subject' => 'New Withdraw Request',
                    'body' => 'User ' . $user->first_name . ' ' . $user->last_name . ' requested to withdraw ' . $requested_withdraw_amount . ' amount.'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Withdraw request submitted successfully.',
                'transaction_type' => 'withdraw'
            ]);
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback on error
            \Log::error('Error submitting withdrawal for user ' . $user->id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during withdrawal request. Please try again.'
            ], 500);
        }
    }

    /**
     * Admin view of transactions.
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminTransactionView(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check: Only admin can access this.
        if (!($user->role === 'admin')) { // Ensure your User model has a 'role' attribute or method
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("tomorrow")),
            'user' => null,
            'status' => null,
        ];

        // Start building query
        $query = Transaction::query();

        // Apply filters from request form
        if ($request->form) {
            if (!empty($request->form['user'])) {
                $users_to_filter = User::where('first_name', 'like', $request->form['user'] . '%')->pluck('id')->toArray();
                $query->whereIn('user_id', $users_to_filter);
            }
            if (!empty($request->form['status'])) {
                $query->where('status', $request->form['status']);
            }
            if (isset($request->form['date_from']) && isset($request->form['date_to'])) {
                $query->whereBetween('created_at', [$request->form['date_from'], $request->form['date_to']]);
            } else {
                $query->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']]);
            }
        } else {
            // Default date filter if no form is provided
            $query->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']]);
        }

        $transactions = $query->get();

        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
        $users_list = User::select_list()->all(); // Renamed to avoid conflict with $users variable

        return response()->json([
            'success' => true,
            'data' => [
                'transactions' => $transactions,
                'users' => $users_list,
                'filter' => $filter_arr
            ]
        ]);
    }

    /**
     * Admin view of balances.
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function adminBalanceView(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check
        if (!($user->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $filter_arr = [
            'user' => null,
        ];

        $query = Balance::query();

        if ($request->form) {
            if (!empty($request->form['user'])) {
                $users_to_filter = User::where('first_name', 'like', $request->form['user'] . '%')->pluck('id')->toArray();
                $query->whereIn('user_id', $users_to_filter);
            }
        }

        $balances = $query->get();

        $filter_arr = ($request->form) ? array_merge($filter_arr, $request->form) : $filter_arr;
        $users_list = User::select_list()->all();

        return response()->json([
            'success' => true,
            'data' => [
                'balances' => $balances,
                'users' => $users_list,
                'filter' => $filter_arr
            ]
        ]);
    }

    /**
     * Get pending withdraw requests list for admin.
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawRequestLists(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check
        if (!($user->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $withdraw_requests = WithdrawRequest::where('status', 'pending')->get();

        return response()->json([
            'success' => true,
            'data' => $withdraw_requests,
            'transaction_type' => 'withdraw'
        ]);
    }

    /**
     * View single withdraw request for admin.
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\WithdrawRequest $withdraw Implicitly bound withdraw request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawRequestListsView(Request $request, WithdrawRequest $withdraw)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check
        if (!($user->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $withdraw
        ]);
    }

    /**
     * Update withdraw request status by admin.
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\WithdrawRequest $withdraw Implicitly bound withdraw request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawRequestListsUpdate(Request $request, WithdrawRequest $withdraw)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check
        if (!($user->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Basic validation for status update (e.g., 'approved', 'rejected')
        $validator = \Validator::make($request->all(), [
            'form.status' => ['required', 'string', \Illuminate\Validation\Rule::in(['approved', 'rejected'])],
            'form.remarks' => ['nullable', 'string', 'max:500'], // Optional remarks
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors(),
                'transaction_type' => 'withdraw'
            ], 422);
        }

        $form = $request->form;
        $old_status = $withdraw->status; // Store old status for checks
        $withdraw->update($form);

        DB::beginTransaction();
        try {
            if ($form['status'] === 'rejected' && $old_status !== 'rejected') { // Prevent double-reversal
                $balance = Balance::where('user_id', $withdraw->user_id)->first();
                if ($balance) {
                    $available_balance = $balance->balance + $withdraw->amount;
                    $balance->update(['balance' => $available_balance]);

                    Transaction::create([
                        'user_id' => $withdraw->user_id,
                        'status' => 'rejected',
                        'amount' => $withdraw->amount,
                        'opening_balance' => $balance->balance, // This would be the balance *before* update
                        'closing_balance' => $available_balance,
                        'remarks' => $form['remarks'] ?? 'Withdrawal request rejected by admin.',
                        'transaction_type' => 'withdraw'
                        
                    ]);
                } else {
                     \Log::warning('Balance record not found for user ' . $withdraw->user_id . ' during withdrawal rejection.');
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating withdraw request or balance for user ' . $withdraw->user_id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the withdrawal request. Please try again.',
                'transaction_type' => 'withdraw'
            ], 500);
        }


        InboxNotification::create([
            'receiver' => $withdraw->user_id,
            'subject' => 'Regarding withdraw status: ' . ucfirst($form['status']),
            'body' => $form['remarks'] ?? 'Your withdrawal request has been ' . $form['status'] . '.'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Withdraw request updated successfully.',
            'transaction_type' => 'withdraw'
        ]);
    }

    /**
     * Approve individual withdraw request (admin).
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the withdraw request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawRequestIndividualUpdate(Request $request, $id)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check
        if (!($user->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Find the withdraw request
        $withdrawRequest = WithdrawRequest::find($id);

        if (!$withdrawRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Withdraw request not found.'
            ], 404);
        }

        if ($withdrawRequest->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Withdraw request is already approved.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $withdrawRequest->update(['status' => 'approved']);

            $transaction = \App\Models\Transaction::where([
                ['user_id', '=', $withdrawRequest->user_id],
                ['amount', '=', $withdrawRequest->amount],
                ['status', '=', 'withdraw'],
            ])->latest()->first();

            if ($transaction) {
                $transaction->update([
                    'status' => 'withdraw_approved',
                    'remarks' => 'Withdrawal approved by admin.',
                ]);
            }

            // $balance = Balance::where('user_id', $withdrawRequest->user_id)->first();
            // $current_balance = $balance ? $balance->balance : 0;

            // $transaction = Transaction::where([
            //     ['user_id', '=', $withdrawRequest->user_id],
            //     ['amount', '=', $withdrawRequest->amount],
            //     ['status', '=', 'withdraw'],
            //     // Optionally, add more conditions (e.g., created_at close to $withdrawRequest->created_at)
            // ])->latest()->first();

            // if ($transaction) {
            //     $transaction->update([
            //         'status' => 'approved',
            //         'remarks' => 'Withdrawal approved by admin.',
            //         // Update other fields if needed
            //     ]);
            // }

            // Optionally, create a transaction record for approval or notify user/admin via inbox
            // based on your business logic for approved withdrawals.
            // Example:
            /*
            InboxNotification::create([
                'receiver' => $withdrawRequest->user_id,
                'subject' => 'Withdrawal Approved',
                'body' => 'Your withdrawal request for ' . $withdrawRequest->amount . ' has been approved.'
            ]);
            */

            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Withdraw request approved successfully.',
                'transaction_type' => 'withdraw'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error approving withdraw request ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while approving the withdrawal request. Please try again.',
                'transaction_type' => 'withdraw'
            ], 500);
        }
    }

    /**
     * Reject individual withdraw request (admin).
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id The ID of the withdraw request.
     * @return \Illuminate\Http\JsonResponse
     */
    public function withdrawRequestIndividualRejectUpdate(Request $request, $id)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check
        if (!($user->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        // Find the withdraw request
        $withdrawRequest = WithdrawRequest::find($id);

        if (!$withdrawRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Withdraw request not found.',
                'transaction_type' => 'withdraw'
            ], 404);
        }

        if ($withdrawRequest->status === 'rejected') {
            return response()->json([
                'success' => false,
                'message' => 'Withdraw request is already rejected.',
                'transaction_type' => 'withdraw'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $withdrawRequest->update(['status' => 'rejected']);

            $balance = Balance::where('user_id', $withdrawRequest->user_id)->first();
            if ($balance) {
                $available_balance = $balance->balance + $withdrawRequest->amount;
                $balance->update(['balance' => $available_balance]);

                Transaction::create([
                    'user_id' => $withdrawRequest->user_id,
                    'status' => 'rejected',
                    'amount' => $withdrawRequest->amount,
                    'opening_balance' => $balance->balance, // Balance *before* update
                    'closing_balance' => $available_balance,
                    'remarks' => 'Withdrawal request rejected by admin. Amount returned to wallet.',
                    'transaction_type' => 'withdraw'
                ]);
            } else {
                \Log::warning('Balance record not found for user ' . $withdrawRequest->user_id . ' during individual withdrawal rejection.');
            }
            DB::commit();

            // Notify user via inbox or email about rejection
            InboxNotification::create([
                'receiver' => $withdrawRequest->user_id,
                'subject' => 'Withdrawal Request Rejected',
                'body' => 'Your withdrawal request for ' . $withdrawRequest->amount . ' has been rejected and the amount returned to your wallet.'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Withdraw request rejected successfully.',
                'transaction_type' => 'withdraw'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error rejecting withdraw request ' . $id . ': ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while rejecting the withdrawal request. Please try again.',
                'transaction_type' => 'withdraw'
            ], 500);
        }
    }

    /**
     * Get Paystack payment report (admin).
     * This method requires admin role authorization.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paystackPaymentReport(Request $request)
    {
        $user = Auth::user(); // Authenticated user via JWT

        // Authorization check
        if (!($user->role === 'admin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("last week saturday")),
            'date_to' => date("Y-m-d", strtotime("today")),
        ];

        // Currently, it fetches all payments without date filtering.
        // If you need date filtering, apply it here.
        $payments = PaymentReport::all(); // Consider adding filtering/pagination if dataset is large

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments,
                'filter' => $filter_arr
            ]
        ]);
    }

    public function userPaymentReport(Request $request)
    {
        $user = Auth::user(); // Get the authenticated user via JWT

        // Define default filter dates for reports
        $filter_arr = [
            'date_from' => date("Y-m-d", strtotime("first day of this month")),
            'date_to' => date("Y-m-d", strtotime("today")),
        ];

        // Start building the query on the PaymentReport model
        // CRUCIAL: Filter by the authenticated user's ID
        $query = PaymentReport::where('user_id', $user->id);

        // Apply filters from request form
        if ($request->form) {
            // Apply date filter
            $dateFrom = $request->form['date_from'] ?? $filter_arr['date_from'];
            $dateTo = $request->form['date_to'] ?? $filter_arr['date_to'];
            $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            $filter_arr['date_from'] = $dateFrom; // Update filter_arr for response
            $filter_arr['date_to'] = $dateTo;
        } else {
            // Apply default date filter if no form data is provided
            $query->whereBetween('created_at', [$filter_arr['date_from'], $filter_arr['date_to']]);
        }

        // Fetch payment reports
        $paymentReports = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Your payment reports retrieved successfully.',
            'data' => [
                'payments' => $paymentReports,
                'filter' => $filter_arr, // Show applied filters
            ]
        ]);
    }
}