<?php


namespace Jlab\Epas\Service;

use Jlab\Epas\Model\PlantItem;

class PlantItemSearch extends ModelSearch
{
    /**
     * @inheritdoc
     * @var int
     */
    public $limit = 1000;

    /**
     * @inheritDoc
     * @return PlantItem
     */
    protected function getModelInstance(){
        return new PlantItem();
    }
}
