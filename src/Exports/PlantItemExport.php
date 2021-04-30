<?php


namespace Jlab\Epas\Exports;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Jlab\Epas\Service\PlantItemSearch;
use Maatwebsite\Excel\Concerns\Exportable;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PlantItemExport implements WithMultipleSheets
{
    use Exportable;
    protected $models;

    public function __construct (Request $request){
        // Build a search object using the request
        $search = new PlantItemSearch();
        $search->limit = 100000;
        $search->applyRequest($request);

        // Build a collection of Plant Items
        $this->models = new Collection($search->getResults());
    }


    /**
     * @return array
     */
    public function sheets(): array
    {
        return [
            'Plant Items' => new PlantItemSheet($this->models),
            'Isolation Points' => new IsolationPointSheet($this->models),
        ];
    }

}