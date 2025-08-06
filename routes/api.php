<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\V1\RegistrationApiController;
use App\Http\Controllers\API\V1\UserApiController;
use App\Http\Controllers\API\V1\LoginApiController;
use App\Http\Controllers\API\V1\UserProfileApiController;
use App\Http\Controllers\API\V1\TransactionApiController;
use App\Http\Controllers\API\V1\BankAccountsControllerApi;
use App\Http\Controllers\API\V1\PaystackApiController;
use App\Http\Controllers\API\V1\PaymentControllerApi;
use App\Http\Controllers\API\V1\ResetPasswordApiController;
use App\Http\Controllers\API\V1\ForgotApiController;
use App\Http\Controllers\API\V1\PaymentController;
use App\User;




/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::prefix('v1')->group(function(){
    	Route::post('demo','API\V1\DemoController@index');
        Route::post('get_token','API\V1\UserApiController@getToken');
        Route::post('get_balance','API\V1\UserApiController@getBalance');
        Route::post('bet','API\V1\UserApiController@betAction');
        Route::post('transaction','API\V1\UserApiController@sportbetTransaction');
        Route::post('transaction-rollback','API\V1\UserApiController@sportbetTransactionRollback');
});

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1/auth')->group(function () {
    Route::post('login', 'API\V1\LoginApiController@login');
    Route::post('send-otp', 'API\V1\LoginApiController@sendOtp');
    Route::post('login/verify-otp', 'API\V1\LoginApiController@verifyOtp');
    
    Route::middleware('api.token.auth')->group(function () {
        Route::post('logout', 'API\V1\LoginApiController@logout');
        Route::post('profile', 'API\V1\UserApiController@profile');
    });

    Route::post('register', 'API\V1\RegistrationApiController@register');
    Route::post('register/verify-otp', 'API\V1\RegistrationApiController@verifyOtp');
    Route::get('email-check/{email}', 'API\V1\RegistrationApiController@emailCheck');
    Route::get('phone-check/{phone}', 'API\V1\RegistrationApiController@phoneCheck');

    // Forgot Password API Routes
    Route::post('send-forgot-email-otp', 'API\V1\ForgotApiController@sendEmailOtp');
    Route::post('verify-forgot-email-otp', 'API\V1\ForgotApiController@verifyEmailOtpAndSendLink');


});
    Route::group(['prefix' => 'v1', 'middleware' => 'auth:api'], function() {
     // Admin only
});
    Route::post('/password/reset', [ResetPasswordApiController::class, 'reset']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/profile/{userId}', [UserProfileAPIController::class, 'show']);
        Route::put('/profile/{userId?}', [UserProfileAPIController::class, 'update']);
    });

// Authenticated routes
Route::middleware('jwt.auth')->group(function () {
    // User transaction routes
    Route::prefix('transactions')->group(function () {
        Route::get('/', [TransactionApiController::class, 'index']);
        Route::get('/deposits', [TransactionApiController::class, 'deposit']);
        Route::get('/withdrawals', [TransactionApiController::class, 'withdraw']);
        
        // Forms data
        Route::get('/deposit-form', [TransactionApiController::class, 'depositForm']);
        Route::get('/withdraw-form', [TransactionApiController::class, 'withdrawForm']);
        
        // Actions
        Route::post('/withdraw', [TransactionApiController::class, 'withdrawAmount']);
        Route::post('/reverse-withdraw/{withdraw}', [TransactionApiController::class, 'reverseWithdraw'])
            ->where('withdraw', '[0-9]+');
    });

    // Admin routes
    Route::middleware('jwt.auth')->prefix('admin')->group(function () {
        // Transaction management
        Route::apiResource('transactions', TransactionApiController::class)->only(['index']);
        Route::get('balances', [TransactionApiController::class, 'adminBalanceView']);
        Route::get('transactions/view', [TransactionApiController::class, 'adminTransactionView']);
        
        // Withdrawal requests
        Route::prefix('withdrawals')->group(function () {
            Route::get('/', [TransactionApiController::class, 'withdrawRequestLists']);
            Route::get('/{withdraw}', [TransactionApiController::class, 'withdrawRequestListsView'])
                ->where('withdraw', '[0-9]+');
            Route::put('/{withdraw}', [TransactionApiController::class, 'withdrawRequestListsUpdate'])
                ->where('withdraw', '[0-9]+');
            Route::patch('/{id}/approve', [TransactionApiController::class, 'withdrawRequestIndividualUpdate'])
                ->where('id', '[0-9]+');
            Route::patch('/{id}/reject', [TransactionApiController::class, 'withdrawRequestIndividualRejectUpdate'])
                ->where('id', '[0-9]+');
            
        });

        
        // Reports
        Route::get('payment-reports', [TransactionApiController::class, 'paystackPaymentReport']);
        
    });
   

    // Other authenticated routes can be added here
});

Route::middleware('jwt.auth')->group(function () { 
    Route::get('user/payment-reports', [TransactionApiController::class, 'userPaymentReport']);
});

Route::prefix('v1')->middleware('jwt.auth')->group(function () {
    // Bank Accounts Endpoints
    Route::prefix('bank-accounts')->group(function () {
        Route::get('/', [BankAccountsControllerApi::class, 'index']);
        Route::get('/bank-list', [BankAccountsControllerApi::class, 'getBankList']);
        Route::post('/add', [BankAccountsControllerApi::class, 'addAccount']);
        Route::put('/activate/{id}', [BankAccountsControllerApi::class, 'activateAccount'])
            ->where('id', '[0-9]+');
        Route::post('/update-banks-list', [BankAccountsControllerApi::class, 'updateBanksList']);
        Route::post('/paystack/create-recipient', [BankAccountsControllerApi::class, 'createTransferRecipient']);
    });
    //Paystack API Endpoints
    Route::post('/paystack/initiate/{id}', [PaystackApiController::class, 'initiate']);
    Route::post('/paystack/bulk-transfer', [PaystackApiController::class, 'bulkTransfer']);
});

Route::prefix('v1')->middleware(['jwt.auth'])->group(function () {
    
    // PayPal Routes
    Route::prefix('paypal')->group(function () {
        Route::post('/payment', [PaymentControllerApi::class, 'paypalPayment']);
        Route::post('/execute', [PaymentControllerApi::class, 'paypalExecutePayment']);
    });

    // Paystack Routes
    Route::prefix('paystack')->group(function () {
        Route::post('/payment', [PaymentControllerApi::class, 'paystackPayment']);
       
       
    });

    // Flutterwave Routes
    Route::prefix('flutterwave')->group(function () {
        Route::post('/payment', [PaymentControllerApi::class, 'flutterwavePayment']);

    });

    

});
Route::prefix('v1')->group(function () {
    Route::prefix('paystack')->group(function () {
        Route::get('/callback', [PaymentControllerApi::class, 'paystackCallback'])->name('api.payment.paystack.callback');
    });
});
Route::prefix('v1')->middleware('jwt.auth')->group(function () {
    Route::prefix('flutterwave')->group(function () {
        Route::get('/callback', [PaymentControllerApi::class, 'flutterwaveCallback'])
            ->name('api.payment.flutterwave.callback');
    });
});

// Route::post('/pay/flutterwave', [PaymentController::class, 'initializeFlutterwavePayment'])->name('flutterwave.pay');
// Route::get('/callback/flutterwave', [PaymentController::class, 'flutterwaveCallback'])->name('flutterwave.callback');


// Fallback route
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint not found'
    ], 404);
});


Route::middleware('jwt.auth')->group(function () {
    // Logout route (requires a valid token to invalidate it)
    Route::post('/logout', [LoginApiController::class, 'logout']);

    // Example of a protected route: Get the authenticated user's details
    // You can access the authenticated user via Auth::user() or $request->user()
    Route::get('/user', function (Request $request) {
        // If this route is reached, the JWT was successfully validated by 'jwt.auth'
        return response()->json([
            'status' => 'SUCCESS',
            'message' => 'User data retrieved successfully',
            'user' => $request->user() // This will return the authenticated user model
        ]);
    });

    // Add all your other API routes that require authentication here.
    // For example:
    // Route::get('/profile', [UserProfileController::class, 'show']);
    // Route::post('/data-entry', [DataEntryController::class, 'store']);
    // Route::put('/update-resource/{id}', [ResourceController::class, 'update']);
});

// Test route for sending Email OTP
Route::prefix('v1')->group(function () {
    Route::post('test/send-email-otp/{user}', function ($userId) {
        $user = User::findOrFail($userId);
        $otp = rand(100000, 999999);
        app(RegistrationApiController::class)->sendEmailOtp($user, $otp);
        return response()->json(['status' => 'sent', 'otp' => $otp]);
    });

    // Test route for sending Mobile OTP
    Route::post('test/send-mobile-otp/{user}', function ($userId) {
        $user = User::findOrFail($userId);
        $otp = rand(100000, 999999);
        app(RegistrationApiController::class)->sendMobileOtp($user, $otp);
        return response()->json(['status' => 'sent', 'otp' => $otp]);
    });
});

Route::get('payment/flutterwave/status', [PaymentControllerApi::class, 'flutterwaveStatusByTxRef']);

