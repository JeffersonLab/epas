<?php

namespace Jlab\Epas\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Jlab\Epas\Model\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Jlab\Epas\Model\PlantItem;
use Jlab\Epas\Model\System;

class CompareWithHco extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant-items:compare-with-hco
                                {--progress-bar : show progress bar on CLI}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync HCO Components with Plant Items';

    /**
     * Message bag holding validation errors.
     *
     */
    protected MessageBag $errors;

    protected bool $progressBar = false;

    protected $dataSource;


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle() {
        $this->errors = new MessageBag();

        // We'll count and report how many changes got made
        $inserts = 0;
        $deletes = 0;
        $updates = 0;

        try {
            $this->progressBar = (php_sapi_name() == 'cli');  // not via web!
            // Inserts & Updates
            foreach ($this->epasSystems() as $system){
                $this->line("Comparing $system...");
                /** @var \Jlab\Epas\Model\Component $component */
                foreach ($system->components as $component){
                    if (! $component->existsInDatabase()){
                        $this->line('Must add ' . $component->name . ' to '. $component->plantParentId());
                        $inserts++;
                    }else{
                        if (! $component->matchesExistingPlantItem()){
                            $existing = $component->plantItem();
                            // Must update existing
                            $this->line("Must update {$existing->description} ({$existing->plant_id}) with HCO changes");
                            foreach ($component->getMismatchedAttributes() as $attribute => $mismatch){
                                $this->line("    {$attribute}: {$mismatch}");
                            }
                            $updates++;
                        }
                    }
                }
            }
            // Removals
            foreach ($this->hcoPlantItems() as $plantItem){
                $component = Component::findByPlantId($plantItem->plant_id);
                if (! $component){
                   $this->line("Must remove {$plantItem->description} - {$plantItem->description}");
                    $deletes++;
                }
            }
        }catch (\Exception $e){
            //$this->error($e->getMessage());
            $this->newLine();
            return 1;
        }

        $this->line($inserts . " Plant Items to be added");
        $this->line($updates . " Plant Items to be updated");
        $this->line($deletes . " Plant Items to be deleted");
        $this->line($this->errors->count() . 'Errors encountered');
        return 0;
    }

    /**
     * The HCO systems that participate in ePAS
     */
    protected function epasSystems() : Collection {
        return System::all()->filter(function($item){
           return $item->hasEpas();
        });
    }

    /**
     * The ePAS Plant Items that have HCO as their data source
     */
    protected function hcoPlantItems() : Collection {
        return PlantItem::where('data_source','HCO')->get();
    }


}
