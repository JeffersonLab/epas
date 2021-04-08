<?php

namespace Jlab\Epas\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlantItemResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = [
            'id' => $this->resource->id,
            'plantId' => $this->resource->plant_id,
            'functionalLocation' => $this->resource->functional_location,
            'plantParentId' => $this->resource->plant_parent_id,
            'description' => $this->resource->description,
            'isIsolationPoint' => $this->resource->is_isolation_point,
            'hasChildren' => $this->resource->hasChildren(),
            'hasIsolationPoints' => $this->resource->hasIsolationPoints(),
        ];
        return $resource;
    }
}
