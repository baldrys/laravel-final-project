<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_users', function (Blueprint $table) {
            $table->increments('id');

            $table->Integer('store_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('store_id')
            ->references('id')->on('stores')
            ->onUpdate('cascade')
            ->onDelete('cascade');

            $table->Integer('user_id')
            ->nullable(true)
            ->unsigned();
            $table->foreign('user_id')
            ->references('id')->on('users')
            ->onUpdate('cascade')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_users');
    }
}
