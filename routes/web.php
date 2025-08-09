<?php

use App\Balance;
use App\KycDocument;
use App\UserBankDetails;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SportsController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// ... (existing routes) ...

Route::get('/', function () {
    // Default URL for guests and fallback
    $defaultUrl = 'https://sptb.igpxl.com/?serverUrl=https%3A%2F%2Fapisptb.igpxl.com&lang=en';

    if (auth::check()) {
        $user = auth()->user();

        if ($user->role != 'admin') {
            try {
                // Set user session data
                $balance = Balance::where('user_id', $user->id)->first()->balance ?? 0.0;
                $kycApproved = KycDocument::where('user_id', $user->id)
                    ->where('status', 'approved')->exists();
                $kycPending = KycDocument::where('user_id', $user->id)
                    ->where('status', 'pending')->exists();
                $hasBankAccount = userBankDetails::where('user_id', $user->id)
                    ->where('Active_status', 'Active')->exists();

                session([
                    'account_status' => $hasBankAccount ? 1 : 0,
                    'kyc_status' => $kycApproved ? 1 : ($kycPending ? 2 : 0),
                    'avail_balance' => $balance
                ]);

                // Get sports iframe URL
                $sportsController = app(SportsController::class);
                $token = $sportsController->getToken();

                if (!$token) {
                    \Log::warning('Failed to get token, using default URL');
                    return view('tradabet-home-page-new', ['iframe_url' => $defaultUrl]);
                }

                $iframe_url = $sportsController->getStartSession($token);

                if (!$iframe_url) {
                    \Log::warning('Failed to get session URL, using default URL');
                    return view('tradabet-home-page-new', ['iframe_url' => $defaultUrl]);
                }

                return view('tradabet-home-page-new', ['iframe_url' => $iframe_url]);

            } catch (\Exception $e) {
                \Log::error('Home page error: '.$e->getMessage());
                return view('tradabet-home-page-new', ['iframe_url' => $defaultUrl]);
            }
        }
        return redirect('/home');
    }

    return view('tradabet-home-page-new', ['iframe_url' => $defaultUrl]);
})->name('/');


Route::get('sports', [SportsController::class, 'sports']);
Route::view('Casino', 'comingsoon');
Route::view('Bingo', 'comingsoon');
Route::view('Virtualbetting', 'comingsoon');
Route::view('Scheduledvirtual', 'comingsoon');
Route::view('Jackpot', 'comingsoon');
Route::view('Promotions', 'comingsoon');
Route::view('Jackpot', 'comingsoon');
Route::view('games', 'menu-pages.games');
Route::view('poker', 'menu-pages.poker');
Route::view('promotions', 'menu-pages.promotions');
Route::view('google', 'googleAuth');
Route::get('/bet', 'BetController@bet');
Route::get('/faqs', 'BetController@faqs');
Route::get('/responsible-gambling', 'BetController@responsibleGambling');
Route::get('/term-conditions', 'BetController@termConditions');
Route::get('/privacy-policy', 'BetController@privacyPolicy');
Route::get('/bonus-terms', 'BetController@bonusTerms');

Route::get('auth/google', 'Auth\LoginController@redirectToGoogle');
Route::get('auth/google/callback', 'Auth\LoginController@handleGoogleCallback');
Route::get('/complete-registration', 'Auth\RegisterController@completeRegistration');
Route::post('register', 'Auth\RegisterController@register');

Route::get('register',function(){
    return redirect ('/');
})->name('register');

Route::get('login',function(){
    return redirect ('/');
})->name('login');

Route::post('login', 'Auth\LoginController@login');
Route::post('login/userVerify','Auth\LoginController@userVerify')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::get('/emailCheck/{postdata}', 'Auth\RegisterController@emailCheck');
Route::get('/phoneCheck/{postdata}', 'Auth\RegisterController@phoneCheck');

// password reset
Route::get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
Route::get('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
Route::get('password/sendOtp', 'Auth\ForgotPasswordController@sendEmailOtp')->name('password.sendOtp');
Route::get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
Route::post('password/reset', 'Auth\ResetPasswordController@reset');

// verify e-mail
Route::get('email/verify', 'Auth\VerificationController@show')->name('verification.notice');
Route::get('email/verify/{id}', 'Auth\VerificationController@verify')->name('verification.verify');
Route::get('email/resend', 'Auth\VerificationController@resend')->name('verification.resend');

//Route::post('register', 'Auth\RegisterController@showRegistrationForm');


Auth::routes(['register'=>false,'login'=>false]);

$middleware=['auth','verified'];

Route::middleware($middleware)->get('/home', 'HomeController@index')->name('home');
    //Bet-list
Route::middleware($middleware)->get('/betlist', 'BetListController@index');
Route::middleware($middleware)->get('/betlist-cashout', 'BetListController@betListCashout');
    //Bonus
Route::middleware($middleware)->get('/active-bonus', 'BonusController@index');
Route::middleware($middleware)->get('/bonus-transaction-list', 'BonusController@bonusTransactionList');
    //Rewards
Route::middleware($middleware)->get('/rewards', 'RewardsController@index');
    //Transaction List
Route::middleware($middleware)->get('/transaction', 'TransactionController@index');
    //Deposit
Route::middleware($middleware)->get('/deposits', 'TransactionController@deposit')->name('deposits');
Route::middleware($middleware)->get('/deposit-form', 'TransactionController@depositForm')->name('deposit-form-add');
Route::middleware($middleware)->get('/deposit-form/{amount}', 'TransactionController@depositForm')->name('deposit-form-add');
Route::middleware($middleware)->get('/payment-request', 'PaymentController@payment');
Route::middleware($middleware)->get('/deposit-request', 'PaymentController@depositAmount')->name('deposit-request');
    //Withdraw
Route::middleware($middleware)->get('/withdraw', 'TransactionController@withdraw')->name('withdraw');
Route::middleware($middleware)->get('/withdraw-request-form', 'TransactionController@withdrawForm')->name('withdraw-request-form');
Route::middleware($middleware)->get('/withdraw-request', 'TransactionController@withdrawAmount');
Route::middleware($middleware)->get('/reverse-withdraw/{withdraw}', 'TransactionController@reverseWithdraw');
Route::middleware($middleware)->get('/transaction-view', 'TransactionController@adminTransactionView');
Route::middleware($middleware)->get('/balance-view', 'TransactionController@adminBalanceView');
Route::middleware($middleware)->get('/withdraw-requests', 'TransactionController@withdrawRequestLists');
Route::middleware($middleware)->get('/withdraw-request/view/{withdraw}', 'TransactionController@withdrawRequestListsView');
Route::middleware($middleware)->get('/withdraw-request/update/{withdraw}', 'TransactionController@withdrawRequestListsUpdate');
Route::middleware($middleware)->get('/withdraw-request-individual/update/{id}', 'TransactionController@withdrawRequestIndividualUpdate');
Route::middleware($middleware)->get('/withdraw-request-individual-reject/update/{id}', 'TransactionController@withdrawRequestIndividualRejectUpdate');
Route::middleware($middleware)->post('/withdraw-request-bulk-reject', 'TransactionController@withdrawRequestBulkRejectUpdate');
Route::middleware($middleware)->get('/transaction-report', 'TransactionController@paystackPaymentReport');
Route::middleware($middleware)->get('/withdraw-list', 'WithdrawListController@index')->name('withdraw.list');

    // user profile
Route::middleware($middleware)->get('users/profile/{user}', 'UserProfileController@show');
Route::middleware($middleware)->get('users/profile/{user}/edit', 'UserProfileController@edit');
Route::middleware($middleware)->patch('users/profile/{user}', 'UserProfileController@update');
Route::middleware($middleware)->get('/developers','DevelopersController@index');

    //KYC
Route::middleware($middleware)->get('/document-upload', 'KycController@index');
Route::middleware($middleware)->get('/kyc-upload-form', 'KycController@documentShow');
Route::middleware($middleware)->post('/kyc-upload', 'KycController@upload');
Route::middleware($middleware)->get('/kyc-list', 'KycController@docList');
Route::middleware($middleware)->get('/kyc-list/view/{document}', 'KycController@viewDoc');
Route::middleware($middleware)->post('/kyc-list/update/{document}', 'KycController@update');
Route::middleware($middleware)->get('/document-show/{id}', 'KycController@show');

//user
Route::middleware($middleware)->get('/user-list', 'UserProfileController@userList');

    //Inbox
/*Route::middleware($middleware)->get('/inbox/mark-all-as-read', 'InboxNotificationController@mark_all_as_read');*/
Route::middleware($middleware)->get('/inbox/message-view/{notification}', 'InboxNotificationController@mark_all_as_read');
Route::middleware($middleware)->resource('inbox', 'InboxNotificationController')->parameters([
        'inbox' => 'inbox_notification'
    ]);

    // Paystack accept payment
Route::post('/pay', 'PaymentController@redirectToGateway')->name('pay');
Route::get('/payment/callback', 'PaymentController@handleGatewayCallback')->name('paystack.callback');

    //BankAccounts
Route::middleware($middleware)->get('/bank-accounts', 'BankAccountsController@index')->name('bank_account');
Route::middleware($middleware)->get('/add-bank-account', 'BankAccountsController@addAccount')->name('add-bank-account');
Route::middleware($middleware)->post('/add_account', 'BankAccountsController@add');

Route::middleware($middleware)->get('/activate-account/{id}', 'BankAccountsController@activateAccount');

    //Paystack transfers
Route::middleware($middleware)->get('/activate-account/{}', 'BankAccountsController@activateAccount');
Route::middleware($middleware)->get('/initiate_transaction/{id}', 'PaystackController@initiate');
Route::middleware($middleware)->get('/finalize_transfer', 'PaystackController@finalizeTransfer')->name('otp');

Route::middleware($middleware)->post('/bulkTransfer', 'PaystackController@bulkTransfer');
Route::middleware($middleware)->get('/updateBanksList', 'BankAccountsController@updateBanksList');

    // Flutterwave
// The route that the button calls to initialize payment
Route::post('/flutterwave_pay', 'PaymentController@initialize')->name('flutterwave_pay');
// The callback url after a payment
Route::get('/rave/callback', 'PaymentController@flutterwaveCallback')->name('callback');

Route::post('/interswitch-pay', 'PaymentController@pay')->name('interswitch-pay');
Route::post('/interswitch-callback', 'PaymentController@interswitch_callback')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/opay-pay', 'PaymentController@opayPay')->name('opay-pay');
Route::any('/opay-return/{reference}', 'PaymentController@opayReturn')->name('opay-return');

Route::post('/sendMobileOtp', 'Auth\RegisterController@sendMobileOtp')->name('send-mobile-otp')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
Route::post('/sendEmailOtp', 'Auth\RegisterController@sendEmailOtp')->name('send-email-otp')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::post('/sendMobileEmailOtp', 'Auth\LoginController@sendMobileEmailOtp')->name('send-mobile-email-otp')->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/dev-autologin', [LoginController::class, 'devAutoLogin'])->name('dev.autologin');

Route::get('/test-otp-send', function (Request $request) {
    $controller = new ForgotPasswordController();
    $request->merge(['email' => 'abhijithpkkrishnan@gmail.com']); // Replace with a real email
    return $controller->sendEmailOtp($request);
});

// Add this route to your web.php file
Route::get('/users/export/csv', 'UserProfileController@exportUsersToCsv')->name('users.export.csv');