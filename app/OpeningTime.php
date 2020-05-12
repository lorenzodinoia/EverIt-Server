<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpeningTime extends Model
{
    public $timestamps = false;
    protected $with = ['openingDay'];

    protected $casts = [
        'opening_time' => 'datetime: H:i',
        'closing_time' => 'datetime H:i',
    ];

    /**
     * Define the one (restaurateur) to many (opening time) relationship
     */
    public function restaurateur() {
        return $this->belongsTo('App\Restaurateur');
    }

    /**
     * Define the one (restaurateur) to many (opening time) relationship
     */
    public function openingDay() {
        return $this->belongsTo('App\OpeningDay');
    }
}
