<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WithdrawRequest extends Model
{
    protected $table = 'withdraw_requests';
    protected $fillable = ['user_id','amount','status','recipient_code'];
    protected $primaryKey = 'id';

    public function user()
    {
        //return $this->belongsTo('Model', 'foreign_key', 'other_key');
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function getStatusDescriptionAttribute(){
        $status=$this->status;
        if($status=='pending'){
            return 'Processing';
        }
        elseif ($status=='approved'){
            return 'Approved';
        }
        elseif ($status=='rejected'){
            return 'Rejected';
        }
        else{
            return 'Reversed';
        }
    }
}
