<?php

namespace Jlab\Epas\Console\Commands;

use Jlab\Epas\Model\PlantItem;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Jlab\Epas\Utility\PlantItemUtility;


class UploadPlantItems extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant-items:upload
                            {--plant-group= : plant group name to use}
                            {--progress-bar : show progress bar on CLI}
                            {--replace : delete any existing rows spreadsheet of same name}
                            {--update : update existing plantids}
                            {file : path to valid excel .xlsx file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload a spreadsheet containing Plant Items';

    /**
     * Message bag holding validation errors.
     *
     * @var MessageBag
     */
    protected $errors;

    protected $progressBar = false;

    protected $dataSource;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->errors = new MessageBag();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try{
            $this->progressBar = (php_sapi_name() == 'cli');  // not via web!
            $this->dataSource = basename($this->argument('file'));
            $items = PlantItemUtility::makeFromSpreadsheet(
                $this->argument('file'),
                $this->option('plant-group')
            );
            //$this->line('Validate:');
            $this->validate($items);
            //$this->line('Insert and Audit:');
            $this->saveAndAudit($items);
            $this->attachIsolationPoints($items);
            return 0;
        }catch(\Exception $e){
            $this->error($e->getMessage());
            $this->newLine();
            return 1;
        }

    }

    /**
     * Perform model self-validation on the models prior to
     * their insertion in the database.
     * @param $items
     */
    protected function validate(Collection $items){
        $row = 1;
        if ($this->progressBar) {
            $bar = $this->output->createProgressBar($items->count());
            $bar->start();
        }
        foreach ($items as $item) {
            $row++;
            if (! $item->validate()){
                foreach ($item->errors()->all() as $message){
                    $this->errors->add("row $row", $message);
                }
            }
            if ($this->progressBar) { $bar->advance();}
        }
        if ($this->progressBar) {
            $bar->finish();
            $this->newLine();
        }

        if ($this->errors->isNotEmpty()){
            $this->displayErrors();
            throw new \Exception("The file contains validation errors and could not be processed. Please fix the errors listed above.");
        }
    }

    /**
     * Inserts the models and then audits them.
     * Uses a database transaction so that if any model fails to insert or pass audit,
     * the transaction is rolled back and no changes are made in the database.
     *
     */
    protected function saveAndAudit(Collection $items){
        try{
            DB::beginTransaction();
            if ($this->option('replace')){
                $deletedRCount = PlantItem::where('data_source',$this->dataSource)->delete();
            }
            if ($this->option('update')) {
                $this->update($items);
            }else{
                $this->insert($items);
            }
            $this->audit($items);
            DB::commit();
        }catch(\Exception $e){
            DB::rollBack();
            throw $e;
        }
    }



    /**
     * Inserts models.
     * Generates error messages if a model already exists.
     * @param $items
     */
    protected function insert(Collection $items){
        $row = 1;
        if ($this->progressBar) {
            $bar = $this->output->createProgressBar($items->count());
            $bar->start();
        }
        foreach ($items as $item) {
            $row++;
            try{
                if (! $this->saveOne($item)){
                    $this->errors->add("row $row ", "insert failed.");
                }
            }catch (\Exception $e){
                $this->errors->add("row $row ", $e->getMessage());
            }
            if ($this->progressBar) { $bar->advance();}
        }
        if ($this->progressBar) {
            $bar->finish();
            $this->newLine();
        }
        if ($this->errors->isNotEmpty()){
            $this->displayErrors();
            throw new \Exception("One or more rows could not be inserted into the database.  No changes were saved.");
        }
    }

    /**
     * Updates or Inserts models.
     *
     * @param $items
     */
    protected function update(Collection $items){
        $row = 1;
        if ($this->progressBar) {
            $bar = $this->output->createProgressBar($items->count());
            $bar->start();
        }
        foreach ($items as $item) {
            $row++;
            try{
                $mergedItem = $this->mergeWithExisting($item);
                if (! $this->saveOne($mergedItem)){
                    $this->errors->add("row $row ", "save failed.");
                }
                $item = $mergedItem;
            }catch (\Exception $e){
                $this->errors->add("row $row ", $e->getMessage());
            }
            if ($this->progressBar) { $bar->advance();}
        }
        if ($this->progressBar) {
            $bar->finish();
            $this->newLine();
        }
        if ($this->errors->isNotEmpty()){
            $this->displayErrors();
            throw new \Exception("One or more rows could not be inserted into the database.  No changes were saved.");
        }
    }

    /**
     * Merges item's properties with those of an existing matching plant item from DB.
     *
     * Returns original if no matching DB item was found.
     *
     * @param $item
     */
    protected function mergeWithExisting(PlantItem $item){
        $targetItem = PlantItem::firstOrNew(['plant_id' => $item->plant_id]);
        $targetItem->fill($item->attributesToArray());
        return $targetItem;
    }

    /**
     * save (insert or update) a single plant item.
     *
     * @param $item
     * @return mixed
     * @throws \Exception
     */
    protected function saveOne($item){
        try{
            return $item->save();
        }catch (\Exception $e){
            if ($e->getCode() === 1){
                $message = "Plant ID \"{$item->plant_id}\" is a duplicate or already exists";
            }else{
                $message = $e->getMessage();
            }
            throw new \Exception($message);
        }
    }

    /**
     * Performs an audit of the items to ensure that plant_parent_id references
     * are valid.
     */
    protected function audit(Collection $items){
        $row = 1;
        if ($this->progressBar) {
            $bar = $this->output->createProgressBar($items->count());
            $bar->start();
        }
        foreach ($items as $item) {
            $row++;
            if ($item->plant_parent_id && ! $item->hasParent()){
                $this->errors->add("row $row ", "Plant Parent Id not found");
            }
            if ($this->progressBar) { $bar->advance();}
        }
        if ($this->progressBar) {
            $bar->finish();
            $this->newLine();
        }
        if ($this->errors->isNotEmpty()){
            $this->displayErrors();
            throw new \Exception("One or more rows have invalid data.  No changes were saved.");
        }
    }


    /**
     * Attaches related plant item that serves as an isolation point
     * if available.
     *
     * @param $items
     */
    protected function attachIsolationPoints(Collection $items){
        $row = 1;
        if ($this->progressBar) {
            $bar = $this->output->createProgressBar($items->count());
            $bar->start();
        }
        foreach ($items as $item) {
            try {
                $row++;
                $plantItem = PlantItem::where('plant_id',$item->plant_id)->first();
                if ($item->isolation_point_plant_id) {
                    $isolationPoint = PlantItem::where('plant_id', strtoupper($item->isolation_point_plant_id))->first();
                    if (!$isolationPoint) {
                        $message = "Isolation Point Plant ID \"{$item->isolation_point_plant_id}\" does not exist";
                        throw new \Exception($message);
                    }
                    $plantItem->isolationPoints()->sync([$isolationPoint->id]);
                }

            }catch (\Exception $e){
                $this->errors->add("row $row ", $e->getMessage());
            }
            if ($this->progressBar) { $bar->advance();}
        }
        if ($this->progressBar) {
            $bar->finish();
            $this->newLine();
        }
        if ($this->errors->isNotEmpty()){
            $this->displayErrors();
            throw new \Exception("Unable to associate one or more isolation points");
        }
    }


    /**
     * Output the contents of the errors MessageBag
     */
    protected function displayErrors(){
        foreach ($this->errors->keys() as $row){
            foreach ($this->errors->get($row) as $message){
                $this->error("Error at row $row : $message");
            }
        }
    }
}
