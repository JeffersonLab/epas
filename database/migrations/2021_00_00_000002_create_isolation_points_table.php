<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIsolationPointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('isolation_points')) {
            Schema::create('isolation_points', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('plant_item_id');
                $table->unsignedInteger('isolation_plant_item_id');

                $table->foreign('plant_item_id')
                    ->references('id')->on('plant_items')
                    ->onDelete('cascade');
                $table->foreign('isolation_plant_item_id')
                    ->references('id')->on('plant_items')
                    ->onDelete('cascade');

                $table->unique(['plant_item_id', 'isolation_plant_item_id']);

            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('isolation_points');
    }
}
