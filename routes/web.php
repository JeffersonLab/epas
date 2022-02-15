<?php

/**********
 * Plant Items
 **********/


use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Facades\Route;

Route::group([
    'middleware' => ['web',SubstituteBindings::class],
    'namespace' => '\Jlab\Epas\Http\Controllers'],
    function () {

          Route::get('plant-items', [
              'as' => 'plant_items.index',
              'uses' => 'PlantItemController@index'
          ]);

          Route::post('plant-items', [
              'as' => 'plant_items.store',
              'uses' => 'PlantItemController@store'
          ]);

          Route::get('plant-items/create', [
              'as' => 'plant_items.create',
              'uses' => 'PlantItemController@create'
          ]);

          Route::get('plant-items/upload-plant-items', [
              'as' => 'plant_items.upload_plant_items_form',
              'uses' => 'PlantItemController@uploadPlantItemsForm'
          ]);

          Route::post('plant-items/upload-plant-items', [
              'as' => 'plant_items.upload_plant_items',
              'uses' => 'PlantItemController@uploadPlantItems'
          ]);

        Route::get('plant-items/upload-isolation-points', [
            'as' => 'plant_items.upload_isolation_points_form',
            'uses' => 'PlantItemController@uploadIsolationPointsForm'
        ]);

        Route::post('plant-items/upload-isolation-points', [
            'as' => 'plant_items.upload_isolation_points',
            'uses' => 'PlantItemController@uploadIsolationPoints'
        ]);

          Route::get('plant-items/table', [
              'as' => 'plant_items.table',
              'uses' => 'PlantItemController@table'
          ]);

          Route::get('plant-items/excel', [
              'as' => 'plant_items.excel',
              'uses' => 'PlantItemController@excel'
          ]);

          Route::get('plant-items/{plantItem}', [
              'as' => 'plant_items.item',
              'uses' => 'PlantItemController@item'
          ]);
});



