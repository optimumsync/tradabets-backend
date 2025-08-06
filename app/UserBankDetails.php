<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserBankDetails extends Model
{
    
    protected $table = 'user_bank_accounts';

    protected $fillable = ['user_id','account_name','account_number','bank_name','bank_code','Active_status','recipient_code','num_type'];

    protected $primaryKey = 'id';

    public function user()
    {
        //return $this->belongsTo('Model', 'foreign_key', 'other_key');
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}


