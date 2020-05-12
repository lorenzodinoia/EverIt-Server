<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpeningDaysRestaurateurTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opening_days_restaurateur', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restaurateur_id');
            $table->unsignedBigInteger('opening_day_id');
            $table->time('opening_time');
            $table->time('closing_time');

            $table->unique(['restaurateur_id', 'opening_day_id', 'opening_time', 'closing_time'], 'opening_unique');

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
        Schema::dropIfExists('opening_days_restaurateur');
    }
}
