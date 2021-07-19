<?php

namespace Jlab\Epas\Service\Filters;

use Illuminate\Database\Eloquent\Builder;

class Search implements Filter
{
    // Value to send to elasticsearch paginator
    // default will be 10 results otherwise!
    public static $perPage = 1000;

    /**
     * @inheritDoc
     */
    public static function apply(Builder $builder, $value)
    {
        // Require minimum of 3 non-whitespace characters
        if (preg_match('/[\w\d]{3,}/', $value)) {
            // We see if we can treat a number as the request to retrieve specific ID
            if (is_numeric($value) && $builder->find($value)) {
                $ids = [$value];
            } else {
                if (method_exists($builder->getModel(),'search')){
                    $ids = $builder->getModel()->search($value)
                        ->paginate(self::$perPage)->getCollection()
                        ->pluck('id');
                }
            }
            $builder->whereIn('id', $ids);
        }
        return $builder;
    }
}//class
