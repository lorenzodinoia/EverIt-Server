<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('riders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->string('surname', 50);
            $table->string('phone_number', 15);
            $table->string('email', 50)->unique();
            $table->string('password');
            $table->string('device_id')->nullable();
            $table->double('last_latitude')->nullable();
            $table->double('last_longitude')->nullable();
            $table->dateTime('location_update')->nullable();
            $table->boolean('operating')->default(false);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('riders');
    }
}
