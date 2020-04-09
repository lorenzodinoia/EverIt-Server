<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    public $timestamps = false;

    public function restaurateur() {
        return $this->belongsTo('App\Restaurateur');
    }

    public function products() {
        return $this->hasMany('App\Product');
    }
}
