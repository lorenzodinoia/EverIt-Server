<?php

use Illuminate\Database\Seeder;
use App\ShopType;

class ShopTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('shop_types')->delete();
        $types = File::get('database/data/shop_type.json');
        $typeList = json_decode($types);
        foreach($typeList->types as $type) {
            $newType = ShopType::create([
                'name' => $type->name
            ]);
            $newType->save();
        }
    }
}
