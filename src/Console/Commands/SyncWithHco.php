<?php

namespace Jlab\Epas\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Jlab\Epas\Model\Component;
use Illuminate\Support\Collection;
use Illuminate\Support\MessageBag;
use Jlab\Epas\Model\PlantItem;
use Jlab\Epas\Model\System;
use Yajra\Pdo\Oci8\Exceptions\Oci8Exception;

class SyncWithHco extends Command{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant-items:sync-with-hco
                                {--progress-bar : show progress bar on CLI}
                                {--disable-search-syncing : disable search syncing during import}';

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
        // We allow the user to disable search syncing which could be useful
        // for large datasets or in dev environments where search indexing is
        // not enabled.
        if ($this->option('disable-search-syncing')) {
            PlantItem::disableSearchSyncing();
        }

        // We'll count and report how many changes got made
        $inserts = 0;
        $deletes = 0;
        $updates = 0;

        try {
            $this->progressBar = (php_sapi_name() == 'cli');  // not via web!
            // Inserts & Updates
            foreach ($this->epasSystems() as $system) {
                /** @var \Jlab\Epas\Model\Component $component */
                foreach ($system->components as $component) {
                    try {
                        if (!$component->existsInDatabase()) {
                            Log::info('Adding ' . $component->name . ' to ' . $component->plantParentId());
                            $plantItem = $component->toPlantItem();
                            if (!$plantItem->save()) {
                                $message = "Failed to save {$plantItem->plant_id} - {$plantItem->description}";
                                $this->errors->add($plantItem->plant_id, $message);
                                Log::error($message);
                                Log::error($plantItem->errors()->toJson());
                            }
                            $inserts++;
                        }
                        else {
                            if (!$component->matchesExistingPlantItem()) {
                                $existing = $component->plantItem();
                                // Must update existing
                                Log::info("Update {$existing->plant_id} with HCO changes");
                                if (!$existing->update($component->toPlantItem()
                                    ->only($component->attributesOfConcern))) {
                                    $message = "Failed to update {$existing->plant_id}";
                                    Log::error($message);
                                    $this->errors->add($existing->plant_id, $message);
                                }
                                $updates++;
                            }
                        }
                    }
                    catch (Oci8Exception $e) {
                        if ($e->getCode() == 2291) {   // Parent Key not found
                            $this->error("Missing Parent Key");  // Report it but continue
                        }
                        else {
                            throw $e;  // Some other DB error that needs attention
                        }
                    }
                }
                // Removals
                foreach ($this->hcoPlantItems() as $plantItem) {
                    $component = Component::findByPlantId($plantItem->plant_id);
                    if (!$component) {
                        Log::info("Must remove {$plantItem->plant_id} - {$plantItem->description}");
                        if (!$plantItem->delete()) {
                            $message = "Failed to delete {$plantItem->plant_id} - {$plantItem->description}";
                            Log::error($message);
                            $this->errors->add($plantItem, $message);
                        }
                        $deletes++;
                    }
                }
            }
        }catch (\Exception $e){
            $this->error($e->getMessage());
            $this->newLine();
            return 1;
        }

        $this->info($inserts . " Plant Items added");
        $this->line($updates . " Plant Items updated");
        $this->line($deletes . " Plant Items deleted");
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
