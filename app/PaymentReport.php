<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentReport extends Model
{
    //
    protected $table = 'payment_transaction_report';
    protected $fillable = ['user_id','amount','status','transaction_reference','recipient_code','transaction_code','payment_at','username','user_email','user_phone'];
    protected $primaryKey = 'id';

    public function user()
    {
        //return $this->belongsTo('Model', 'foreign_key', 'other_key');
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
