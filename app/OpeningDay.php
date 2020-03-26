<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpeningDay extends Model
{
    public function openingTimes(){
        return $this->hasMany('App\OpeningTime');
    }
}
