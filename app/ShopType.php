<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ShopType extends Model
{
    /**
     * Define one (shop type) to many (restaurateurs) relationship
     */
    public function restaurateurs() {
        return $this->hasMany('App\Restaurateur');
    }
}
