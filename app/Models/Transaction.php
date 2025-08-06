<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //

    protected $table = 'transaction';
    protected $fillable = ['user_id','status','amount','closing_balance','opening_balance', 'transaction_id', 'remarks','transaction_type'];
    protected $primaryKey = 'id';

    public function user()
    {
        //return $this->belongsTo('Model', 'foreign_key', 'other_key');
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
    public function getStatusDescriptionAttribute(){
        $status=$this->status;
        if($status=='deposit'){
            return 'Deposit';
        }
        elseif ($status=='withdraw'){
            return 'Withdraw';
        }
        elseif ($status=='reversed'){
            return 'Reversed';
        }
        elseif ($status=='rejected'){
            return 'Rejected';
        }
        elseif ($status=='request'){
            return 'Request';
        }
        elseif ($status=='failed'){
            return 'Failed';
        }
        elseif ($status=='approved'){
            return 'approved';
        }
        else{
            return 'Bonus';
        }
    }

}
