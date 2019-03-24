<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\Support\Enums\OrderStatus;

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
            $table->increments('id');

            $table->Integer('store_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('store_id')
            ->references('id')->on('stores')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->Integer('customer_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('customer_id')
            ->references('id')->on('users')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->enum('status', OrderStatus::getValues())->default('Placed');
            $table->float('total_price', 8, 2);
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
