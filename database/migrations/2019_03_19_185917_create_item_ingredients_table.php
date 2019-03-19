<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemIngredientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_ingredients', function (Blueprint $table) {
            $table->increments('id');

            $table->Integer('item_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('item_id')
            ->references('id')->on('items')
            ->onUpdate('cascade')
            ->onDelete('cascade');
            
            $table->Integer('ingredient_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('ingredient_id')
            ->references('id')->on('item_ingredient')
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
        Schema::dropIfExists('item_ingredients');
    }
}
