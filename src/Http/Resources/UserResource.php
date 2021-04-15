<?php


namespace Jlab\Epas\Http\Resources;


class UserResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = parent::toArray($request);
        $resource['isAdmin'] = $this->resource->is_admin ? true : false;
        return $resource;
    }
}
