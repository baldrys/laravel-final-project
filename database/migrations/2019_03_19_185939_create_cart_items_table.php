<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->increments('id');

            $table->Integer('user_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('user_id')
            ->references('id')->on('users')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            
            $table->Integer('item_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('item_id')
            ->references('id')->on('items')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->integer('amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
}
