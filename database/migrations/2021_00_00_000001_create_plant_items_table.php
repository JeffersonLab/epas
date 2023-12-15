<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePlantItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('plant_items')) {
            Schema::create('plant_items', function (Blueprint $table) {
                $table->id();
                $table->timestamps();
                $table->timestamp('integrated_at', 6)->nullable();
                $table->string('plant_parent_id', 255)->index()->nullable();
                $table->string('plant_id', 255)->unique();
                $table->string('functional_location', 255)->nullable();
                $table->string('parent_functional_location', 255)->nullable();
                $table->string('asset_management_id', 100)->nullable();
                $table->string('description', 500);
                $table->string('location', 100)->nullable();
                $table->string('code', 500)->nullable();
                $table->string('plant_group', 255);
                $table->string('plant_type', 255)->nullable();
                $table->string('default_restore_condition', 200)->nullable();
                $table->string('default_isolation_condition', 200)->nullable();
                $table->boolean('is_plant_item')->nullable();
                $table->boolean('is_isolation_point')->nullable();
                $table->boolean('is_confined_space')->nullable();
                $table->boolean('is_safety_system')->nullable();
                $table->string('barcode_id', 50)->nullable();
                $table->string('equipment_num', 100)->nullable();
                $table->boolean('is_temporary_item')->nullable();
                $table->string('plant_identifier', 100)->nullable();
                $table->string('isolation_point_num', 100)->nullable();
                $table->string('switchboard_cubicle_number', 255)->nullable();
                $table->string('circuit_voltage', 50)->nullable();
                $table->boolean('is_critical_plant')->nullable();
                $table->string('drawing_reference', 100)->nullable();
                $table->boolean('is_limited_authority')->nullable();
                $table->string('method_of_proving', 255)->nullable();
                $table->boolean('is_passing_valve')->nullable();
                $table->string('data_source', 255);
                $table->string('data_source_id', 255)->nullable();
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
        Schema::dropIfExists('plant_items');
    }
}
