<?php

namespace Jlab\Epas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\ResponseFactory;


class SetPlantItemRootView{

    /**
     * Make sure that the package root view gets used if the
     * caller hasn't made provision otherwise.
     *
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->routeIs('plant_items.*')){
            Inertia::setRootView(config('epas.root_view','jlab-epas::app'));
        }
        return $next($request);
    }
}
