<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('delivery_address');
            $table->dateTime('estimated_delivery_time');
            $table->text('order_notes')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->string('validation_code', 5);
            $table->dateTime('actual_delivery_time')->nullable();
            $table->unsignedInteger('status')->default(0);
            $table->double('longitude');
            $table->double('latitude');
            $table->boolean('late')->default(0);
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('rider_id')->nullable();
            $table->unsignedBigInteger('restaurateur_id');
            $table->timestamps();

            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('rider_id')->references('id')->on('riders')->onDelete('cascade');
            $table->foreign('restaurateur_id')->references('id')->on('restaurateurs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
