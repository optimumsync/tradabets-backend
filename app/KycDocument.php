<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class KycDocument extends Model
{
    protected $table = 'kyc_documents';
    protected $fillable = ['user_id','image_data','document_type','remarks','status','name', 'id_number'];
    protected $primaryKey = 'id';


    public function user()
    {
        //return $this->belongsTo('Model', 'foreign_key', 'other_key');
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
