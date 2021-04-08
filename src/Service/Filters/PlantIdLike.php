<?php


namespace Jlab\Epas\Service\Filters;


use Illuminate\Database\Eloquent\Builder;

class PlantIdLike implements Filter
{

    /**
     * @inheritDoc
     */
    public static function apply(Builder $builder, $value)
    {
        return $builder->where('plant_id', 'like',strtoupper('%'.$value.'%'));
    }
}
