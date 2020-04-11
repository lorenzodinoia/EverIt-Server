<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpeningTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opening_times', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->unsignedBigInteger('restaurateur_id');
            $table->unsignedBigInteger('opening_day_id');

            $table->foreign('restaurateur_id')->references('id')->on('restaurateurs')->onDelete('cascade');
            $table->foreign('opening_day_id')->references('id')->on('opening_days')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opening_times');
    }
}
