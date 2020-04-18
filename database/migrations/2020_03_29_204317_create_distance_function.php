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
        DROP PROCEDURE IF EXISTS DISTANCE;
        CREATE DEFINER=`root`@`localhost` FUNCTION `DISTANCE`(lat1 FLOAT, lon1 FLOAT, lat2 FLOAT, lon2 FLOAT) RETURNS float
            NO SQL
            DETERMINISTIC
        BEGIN
            DECLARE r FLOAT UNSIGNED DEFAULT 6372.8;
            DECLARE dLat FLOAT UNSIGNED;
            DECLARE dLon FLOAT UNSIGNED;
            DECLARE a FLOAT UNSIGNED;
            DECLARE c FLOAT UNSIGNED;

            SET dLat = ABS(RADIANS(lat2 - lat1));
            SET dLon = ABS(RADIANS(lon2 - lon1));
            SET lat1 = RADIANS(lat1);
            SET lat2 = RADIANS(lat2);

            SET a = POW(SIN(dLat / 2), 2) + COS(lat1) * COS(lat2) * POW(SIN(dLon / 2), 2);
            SET c = 2 * ASIN(SQRT(a));

            RETURN (r * c);
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
