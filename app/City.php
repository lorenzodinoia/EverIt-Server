<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    public $timestamps = false;

    /**
     * Define one (city) to many (restaurateurs) relationship
     */
    public function restaurateurs() {
        return $this->hasMany('App\Restaurateur');
    }
}
