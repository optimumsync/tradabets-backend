<?php

namespace App;

use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
// REMOVE THIS LINE IF YOU ARE NOT USING LARAVEL PASSPORT FOR ANY OTHER AUTHENTICATION PURPOSES
// use Laravel\Passport\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject; // Keep this import

class User extends Authenticatable implements JWTSubject // <-- ADD 'implements JWTSubject' here
{
    // REMOVE HasApiTokens if you are not using Laravel Passport for any other authentication purposes
    use Notifiable; // Removed HasApiTokens from here

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = 'user';
    protected $fillable = [
        'first_name','last_name','email', 'password','country_code','phone','country','date_of_birth','city','state',
        'token',
        'is_active' 
    ];
    protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
        'google2fa_secret'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean'
    ];

    /**
     * Encrypt the user's google_2fa secret.
     *
     * @param  string  $value
     * @return string
     */
    public function setGoogle2faSecretAttribute($value)
    {
        $this->attributes['google2fa_secret'] = encrypt($value);
    }

    /**
     * Decrypt the user's google_2fa secret.
     *
     * @param  string  $value
     * @return string
     */
    public function getGoogle2faSecretAttribute($value)
    {
        return decrypt($value);
    }

    // --- Add these two methods required by the JWTSubject interface ---

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // This will return the value of your primary key (id)
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            // You can add any custom data to the JWT payload here.
            // For example, if you have a 'role' column:
            // 'role' => $this->role,
        ];
    }

    // --- Your existing relations and helper methods ---

    /**
     * User tokens relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens()
    {
        return $this->hasMany(Token::class);
    }

    /**
     * Return the country code and phone number concatenated
     *
     * @return string
     */
    public function getPhoneNumber()
    {
        return $this->country_code.$this->phone;
    }
    public function transaction()
    {
        return $this->hasMany('App\Models\Transaction', 'user_id', 'id');
    }
    public function balance()
    {
        return $this->hasMany('App\Balance', 'user_id', 'id');
    }

    // MODIFIED: Renamed this method from withdraw() to withdrawals() to match the controller and view.
    public function withdrawals()
    {
        return $this->hasMany('App\WithdrawRequest', 'user_id', 'id');
    }
    public function kycDocument()
    {
        return $this->hasMany('App\kycDocument', 'user_id', 'id');
    }

    /**
     * Get all of the user's bank accounts.
     */
    public function userBankDetails()
    {
        return $this->hasMany('App\UserBankDetails', 'user_id', 'id');
    }

    /**
     * NEW: Get the user's active bank account.
     */
    public function activeBankAccount()
    {
        return $this->hasOne('App\UserBankDetails', 'user_id', 'id')->where('Active_status', 'Active');
    }

    // ADDED: This method is required for the user details page to load deposits.
    /**
     * Get the deposits for the user.
     */
  public function deposits()
    {
        // This relationship now uses your existing Transaction model.
        // It assumes you have a column to identify the transaction type.
        return $this->hasMany('App\Models\Transaction', 'user_id', 'id')->where('transaction_type', 'Deposit');
    }

    public static function select_list()
    {
        // get
        $keyed = User::get()->mapWithKeys(function($item) { // Changed 'user' to 'User' (class name)
            return [$item['id'] => $item['first_name'].' '.$item['last_name']];
        });

        return $keyed;
    }

    /**
     * Send e-mail verification notification.
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }

    /**
     * Send password reset notification.
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function getRouteKeyName()
    {
        return 'id';
    }
}