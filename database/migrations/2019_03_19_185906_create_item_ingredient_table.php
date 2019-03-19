<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemIngredientTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_ingredient', function (Blueprint $table) {
            $table->increments('id');

            $table->Integer('store_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('store_id')
            ->references('id')->on('stores')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->string('name');
            $table->float('price', 8, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('item_ingredient');
    }
}
