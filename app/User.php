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
        'token' // <-- REMOVE 'token' from fillable if it was used for the old custom token system
    ];
    protected $primaryKey = 'id';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
        // If you had a 'token' column in your 'user' table for the old system, and you're now using JWT,
        // you should either remove the 'token' column from your database entirely or keep it hidden
        // but understand it's NOT used by JWTAuth for authentication.
        // 'token',
        // Also consider adding phone_otp and email_otp here if they are directly on the User model
        // and you want them hidden from JSON responses.
        // 'phone_otp',
        // 'email_otp',
        'google2fa_secret' // If you want to hide the encrypted 2FA secret
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
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
        // If this 'tokens' relation was for Laravel Passport or your old custom tokens,
        // you might need to reconsider its purpose or remove it if no longer needed.
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
    public function withdraw()
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