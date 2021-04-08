<?php

namespace Jlab\Epas\Http\Resources;

class PlantItemDetailResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = $this->camelArray($this->resource->getAttributes());
        if ($this->resource->hasIsolationPoints()){
            $resource['isolationPoints'] = $this->isolationPoints();
        }
        $resource['can'] = $this->can($request->user());
        return $resource;
    }

    protected function isolationPoints(){
        $points = [];
        foreach ($this->resource->isolationPoints as $point){
            $points[] = [
                'id'    => $point->id,
                'plantId'    => $point->plant_id,
                'description' => $point->description,
            ];
        }
        return $points;
    }
}
