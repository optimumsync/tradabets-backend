<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $table = 'balance';
    protected $fillable = ['user_id','balance'];
    protected $primaryKey = 'id';

    public function user()
    {
        //return $this->belongsTo('Model', 'foreign_key', 'other_key');
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
