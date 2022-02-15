<?php
/*
 * PlantItemController routes
 */

use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => SubstituteBindings::class,
    'prefix' => 'api',
    'namespace' => '\Jlab\Epas\Http\Controllers'],
    function () {
        Route::get('plant-items', [
            'as' => 'api.plant_items.index',
            'uses' => 'PlantItemApiController@index'
        ]);

        Route::post('plant-items', [
            'as' => 'api.plant_items.store',
            'uses' => 'PlantItemApiController@store'
        ]);

        Route::get('plant-items/children', [
            'as' => 'api.plant_items.children',
            'uses' => 'PlantItemApiController@children'
        ]);

        Route::get('plant-items/data/isolation-points', [
            'as' => 'api.plant_items.data.isolation_points',
            'uses' => 'PlantItemApiController@isolationPoints'
        ]);

//        Route::post('plant-items/upload-plant', [
//            'as' => 'api.plant_items.upload_plant',
//            'uses' => 'PlantItemApiController@uploadPlant'
//        ]);

        Route::get('plant-items/{plantItem}', [
            'as' => 'api.plant_items.item',
            'uses' => 'PlantItemApiController@item'
        ]);

        Route::put('plant-items/{plantItem}', [
            'as' => 'api.plant_items.update',
            'uses' => 'PlantItemApiController@update'
        ]);

        Route::delete('plant-items/{plantItem}', [
            'as' => 'api.plant_items.delete',
            'uses' => 'PlantItemApiController@delete'
        ]);
    }
);
