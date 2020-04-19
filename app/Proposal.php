<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $with = ['order'];

    public function restaurateur(){
        return $this->belongsTo('App\Restaurateur');
    }

    public function riders(){
        return $this->belongsTo('App\Rider', 'rider_id');
    }

    public function order(){
        return $this->belongsTo('App\Order');
    }
}
