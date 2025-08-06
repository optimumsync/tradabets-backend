<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Demo extends Model
{
    protected $table = 'demo';
    protected $fillable =['total_tax','user_id'];
    protected $primaryKey = 'id';
}
?>
