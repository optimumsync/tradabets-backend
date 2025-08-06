<?php

namespace App\Http\Controllers\Auth;

use App\Balance;
use App\Models\Transaction;
use App\User;
//use App\Models\MultiTenant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Cookie;

//use App\Helpers\MultiTenantSeedingHelper;
use App\Helpers\BaseHelper;
use Twilio\Rest\Client;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/login';

    /**
     * The MultiTenant.
     *
     * @var string
     */
    protected $multi_tenant = null;

    /**
     * The MultiTenant UUID.
     *
     * @var string
     */
    protected $multiTenantUUID = null;

    /**
     * The tenant domain.
     *
     * @var string
     */
    protected $domain = null;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');

       // $this->set_domain();
        //$this->set_multi_tenant();
    }

    /**
     * Set the tenant domain.
     *
     * @return void
     */
    protected function set_domain()
    {
        $this->domain = (isset($_SERVER['SERVER_NAME'])) ? strtolower(trim($_SERVER['SERVER_NAME'])) : null;
    }

    /**
     * Set the multi tenant uuid.
     *
     * @return void
     */
   /* protected function set_multi_tenant()
    {
        $multi_tenant = MultiTenant::where('domain', $this->domain)->get();
        $multi_tenant = ($multi_tenant) ? $multi_tenant->first() : null;

        // set
        $this->multi_tenant = ($multi_tenant) ? $multi_tenant : null;
        $this->multiTenantUUID = ($multi_tenant) ? $multi_tenant->uuid : null;
    }*/

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        //$multi_tenant_uuid = $this->multiTenantUUID;

        return Validator::make($data, [
            /*'tenant_title' => ['required', 'string', 'min:3', 'max:150'],*/
            'first_name' => ['required', 'string', 'max:150'],
            'last_name' => ['required', 'string', 'max:150'],
            'country' =>['required','string','max:150'],
            'state'=> ['required','string','max:150'],
            'city'=> ['required','string','max:150'],
            'phone' =>['required','string'],
            'password' => ['required', 'string', 'min:6', 'max:50', 'required_with:password_confirmation', 'same:password_confirmation'],
            'password_confirmation' => ['required', 'string', 'min:6', 'max:50']
        ], [], [
            'first_name' => 'Name',
            'last_name' => 'Surname',
            'password' => 'Password',
            'phone' => 'Phone',
            'country'=> 'Country',
            'state' => 'State',
            'city' => 'City'
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // set
       /* $seed_multi_tenant = ($this->multiTenantUUID) ? false : true;

        // check / create
        if(!$this->multiTenantUUID){
            $multi_tenant = MultiTenant::create([
                'title' => $data['tenant_title'],
                'domain' => $this->domain,
                'status' => 1
            ]);

            $this->multiTenantUUID = $multi_tenant->uuid;
        }*/

        // create
        $user = User::create([
            /*'title' => $data['title'],
            'initials' => $data['initials'],*/
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            /*'user_name' => $data['email'],*/
            'email' => strlen($data['email']) > 0 ? $data['email'] : null,
            'date_of_birth'=>$data['date_of_birth'],
            'country_code' =>$data['country_code'],
            'phone' =>$data['phone'],
            'password' => Hash::make($data['password']),
            'country'=>$data['country'],
            'state'=>$data['state'],
            'city'=>$data['city'],
            'token'=>BaseHelper::generateToken()
            /*'multi_tenant_uuid' => $this->multiTenantUUID,
            'status' => 1*/
        ]);

        // seed
        /*if($seed_multi_tenant){
            MultiTenantSeedingHelper::seed_tenant($this->multiTenantUUID, $user);
        }*/

        // reset
       /* Cookie::queue(Cookie::forget('ACCEPTED_TERMS'));
        Cookie::queue(Cookie::forget('ACCEPTED_PRIVPOL'));*/

        return $user;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    /*public function showRegistrationForm()
    {
        $view_data = ['multi_tenant' => $this->multi_tenant];

        return view('auth.register', $view_data);
    }*/

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // validate
        $this->validator($request->all())->validate();

        // create
        //event(new Registered($user = $this->create($request->all())));

        // create
        $user = $this->create($request->all());

        Balance::create([
            'user_id'=>$user->id,
            'balance'=>Config::get('constants.default_bonus'),
        ]);

        Transaction::create(
            [
                'user_id'=>$user->id,
                'status'=>'bonus',
                'amount'=>Config::get('constants.default_bonus'),
                'opening_balance'=>0,
                'closing_balance'=>Config::get('constants.default_bonus'),
                'remarks'=>'Registration Bonus'
            ]
        );

        // send notification
        //$user->sendEmailVerificationNotification();

        return $this->registered($request, $user) ?: redirect('/');
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }
    public function emailCheck($postData)
    {
        // data
        $email_check=User::where('email',$postData)->get()->all();
        if($email_check!=null)
        {
            return 0;
        }
        else
        {
            return 1;
        }
        // view
    }
    public function phoneCheck($postData)
    {
        // data
        $phone_check=User::where('phone',$postData)->get()->all();
        if($phone_check!=null)
        {
            return 0;
        }
        else
        {
            return 1;
        }
        // view
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }

    public function sendOTP(Request $request)
    {

        //mobile otp
        $phoneOtp= rand(100000, 999999);
        $account_sid = getenv('TWILIO_ACCOUNT_SID');
        $auth_token = getenv('TWILIO_AUTH_TOKEN');
        $twilio_number = "+13133563321";
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $request->phone,
            array(
                'from' => $twilio_number,
                'body' => 'Mobile verification OTP is: '. $phoneOtp
            )
        );

        //email otp
        $emailOtp = rand(100000, 999999);
        $details = [
            'title' => 'Tradabets Registration OTP',
            'email_otp' => $emailOtp
        ];

        \Mail::to($request->email)->send(new \App\Mail\OtpEmail($details));

        $response=[
            'phone_otp'=>$phoneOtp,
            'email_otp'=>$emailOtp
        ];

        return response()->json($response);
    }

    public function sendMobileOtp(Request $request)
    {
        //mobile otp
        $phoneOtp= rand(100000, 999999);
        $account_sid = getenv('TWILIO_ACCOUNT_SID');
        $auth_token = getenv('TWILIO_AUTH_TOKEN');
        $twilio_number = "+13133563321";
        $client = new Client($account_sid, $auth_token);
        $client->messages->create(
            $request->phone,
            array(
                'from' => $twilio_number,
                'body' => 'Mobile verification OTP is: '. $phoneOtp
            )
        );

        $response=[
            'phone_otp'=>$phoneOtp
        ];

        return response()->json($response);
    }

    public function sendEmailOtp(Request $request)
    {
        //email otp
        $emailOtp= rand(100000, 999999);

        //\Mail::to($request->email)->send(new \App\Mail\OtpEmail($details));
        $content = '<h1>Tradabets Registration OTP:</h1><p>'.$emailOtp.'</p>';
        $from_name = $request->first_name." ".$request->last_name;
        $subject = "Tradabets Registration OTP";

        BaseHelper::sendGridMail($request->email, $content, $from_name, $subject);

        $response=[
            'email_otp'=>$emailOtp
        ];

        return response()->json($response);
    }
}
