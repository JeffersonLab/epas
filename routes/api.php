<?php
/*
 * PlantItemController routes
 */

use Illuminate\Support\Facades\Route;

Route::get('plant-items', [
    'as' => 'api.plant_items.index',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@index'
]);

Route::post('plant-items', [
    'as' => 'api.plant_items.store',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@store'
]);

Route::get('plant-items/children', [
    'as' => 'api.plant_items.children',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@children'
]);

Route::get('plant-items/data/isolation-points', [
    'as' => 'api.plant_items.data.isolation_points',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@isolationPoints'
]);

Route::post('plant-items/upload', [
    'as' => 'api.plant_items.upload',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@upload'
]);

Route::get('plant-items/{plantItem}', [
    'as' => 'api.plant_items.item',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@item'
]);

Route::put('plant-items/{plantItem}', [
    'as' => 'api.plant_items.update',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@update'
]);

Route::delete('plant-items/{plantItem}', [
    'as' => 'api.plant_items.delete',
    'uses' => '\Jlab\Epas\Http\Controllers\PlantItemApiController@delete'
]);
