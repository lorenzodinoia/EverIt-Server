<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistanceFunction extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
        CREATE FUNCTION DISTANCE (clat DOUBLE, clng DOUBLE, rlat DOUBLE, rlng DOUBLE)
        RETURNS INTEGER
        DETERMINISTIC
        BEGIN
            DECLARE decimal_distance DOUBLE;
            DECLARE distance INT;
            SET decimal_distance = ( 6371 * acos( cos( radians(clat) ) 
            * cos( radians( rlat ) ) 
            * cos( radians( rlng ) - radians(clng) ) + sin( radians(clat) ) 
            * sin( radians( rlat ) ) ) ); 
            SET distance = FLOOR(decimal_distance);
            RETURN distance;
        END
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS DISTANCE');
    }
}
