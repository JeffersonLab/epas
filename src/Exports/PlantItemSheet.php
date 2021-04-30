<?php


namespace Jlab\Epas\Exports;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class PlantItemSheet implements FromArray, WithTitle, WithHeadings
{
    protected $models;

    public function __construct (Collection $models){
        $this->models = $models;
    }

    public function array(): array
    {
        return $this->models->map(function ($plantItem) {
            return $plantItem->only($plantItem->getFillable());
        })->toArray();
    }

    public function headings(): array{
        return $this->models->first()->getFillable();
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Plant Items';
    }
}