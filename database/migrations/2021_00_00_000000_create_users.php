<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsers extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // For testing purposes we need a users table with
        // an is_admin field.
        if (config('app.env') === 'testing'){
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('email',255)->nullable();
                $table->string('password')->nullable()->default('*lck*');
                $table->string('firstname');
                $table->string('lastname');
                $table->boolean('is_admin')->default(false);
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function down()
    {
        // For testing purposes we need a users table with
        // an is_admin field.
        if (config('app.env') === 'testing'){
            Schema::drop('users');
        }
    }
}
