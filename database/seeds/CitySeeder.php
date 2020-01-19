<?php

use Illuminate\Database\Seeder;
use App\City;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('cities')->delete();
        $cities = File::get('database/data/bari.json');
        $cityList = json_decode($cities);
        foreach($cityList->cities as $city) {
            $newCity = City::create([
                'name' => $city->name . ' '. '(' . $city->country_code . ')'
            ]);
            $newCity->save();
        }
    }
}