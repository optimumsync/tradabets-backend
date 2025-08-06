<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BanksList extends Model
{
    //
    protected $table = 'banks_list';
    protected $fillable = ['bank_name', 'bank_code', 'country', 'currency', 'type', 'bank_list_id'];
    
}
