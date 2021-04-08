<?php

use Faker\Generator as Faker;

$factory->define(Jlab\Epas\Model\PlantItem::class, function (Faker $faker) {
    return [
        'plant_id' => $faker->text(20),
        'description' => $faker->sentence(),
        'plant_group' => 'Accelerator',   // a value from config/epas.php
        'data_source' => 'test',
    ];
});
