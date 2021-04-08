<?php


namespace Jlab\Epas\Http\Resources;


use Illuminate\Http\Resources\Json\ResourceCollection;

class PlantItemCollection extends ResourceCollection
{
    /**
     * The resource that this resource collects.
     *
     * @var string
     */
    public $collects = PlantItemResource::class;


}
