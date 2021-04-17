<?php
/**
 * Created by PhpStorm.
 * User: theo
 * Date: 6/9/17
 * Time: 8:45 AM
 */

namespace Jlab\Epas\Service\Filters;

use Illuminate\Database\Eloquent\Builder;

class DataSource implements Filter
{

    /**
     * Apply a given search value to the builder instance.
     *
     * @param Builder $builder
     * @param mixed $value  integer
     * @return Builder $builder
     */
    public static function apply(Builder $builder, $value)
    {
        if ($value){
            return $builder->where('data_source', $value);
        }
        return $builder;
    }
}
