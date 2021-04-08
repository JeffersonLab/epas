<?php

namespace Jlab\Epas\Service\Filters;

use Illuminate\Database\Eloquent\Builder;

class IsIsolationPoint implements Filter
{

    /**
     * @inheritDoc
     */
    public static function apply(Builder $builder, $value)
    {
        // Here we treat null as a positive request because it
        // indicates a query string like ?isIsolationPoint.
        if ($value === null || (string) $value !== '0'){
            return $builder->where('is_isolation_point', true);
        }

        return $builder;
    }
}
