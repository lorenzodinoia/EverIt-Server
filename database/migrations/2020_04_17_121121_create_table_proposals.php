<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProposals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proposals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rider_id');
            $table->unsignedBigInteger('restaurateur_id');
            $table->unsignedBigInteger('order_id');
            $table->time('pickup_time');
            $table->timestamps();

            $table->foreign('rider_id')->references('id')->on('riders')->onDelete('restrict');
            $table->foreign('restaurateur_id')->references('id')->on('restaurateurs')->onDelete('restrict');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('restrict');

            $table->unique(['rider_id', 'restaurateur_id', 'order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('proposals');
    }
}
