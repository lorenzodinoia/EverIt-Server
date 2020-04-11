<?php

use Illuminate\Database\Seeder;
use App\OpeningDay;

class OpeningDaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('opening_days')->delete();
        $days = File::get('database/data/days.json');
        $dayList = json_decode($days);
        foreach($dayList->days as $city) {
            $newDay = OpeningDay::create([
                'name' => $city->name
            ]);
            $newDay->save();
        }
    }
}
