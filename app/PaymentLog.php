<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentLog extends Model
{
    // type=1(payme)
    public $fillable = ['id', 'user_id', 'type', 'summa'];

    public function user(){
        return $this->belongsTo('\App\User','user_id','id');
    }
}
