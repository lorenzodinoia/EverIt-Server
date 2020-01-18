<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRestaurateursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('restaurateurs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('shop_name');
            $table->string('address');
            $table->string('cap', 5);
            $table->string('phone_number', 15);
            $table->string('email')->unique();
            $table->string('password');
            $table->string('piva', 11);
            $table->text('description')->nullable();
            $table->float('delivery_cost', 4, 2)->default(0);
            $table->integer('min_quantity')->default(1)->unsigned();
            $table->integer('order_range_time')->default(10)->unsigned();
            $table->string('image_path')->nullable();
            $table->unsignedBigInteger('shop_type_id');
            $table->unsignedBigInteger('city_id');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('shop_type_id')->references('id')->on('shop_types')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('restaurateurs');
    }
}
