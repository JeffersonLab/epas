<?php

namespace Jlab\Epas\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use Jlab\Epas\Model\PlantItem;
use Jlab\Epas\Utility\PlantItemUtility;

class UploadIsolationPoints extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant-items:upload-isolation-points
                            {--progress-bar : show progress bar on CLI}
                            {--replace : replace existing isolation points}
                            {--sheet=2: the number of the spreadsheet tab containing the isolation points data} 
                            {file : path to valid excel .xlsx file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload isolation points data from a spreadsheet.';

    /**
     * Message bag holding validation errors.
     *
     * @var MessageBag
     */
    protected $errors;

    /**
     * Whether to display a progress bar.
     *
     * @var bool
     */
    protected $progressBar = false;

    /**
     * Plant Ids that have already been processed.
     */
    protected $processed = [];

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
        // Re-initialize the processed list in case the current command instance
        // gets reused, for example during testing where we load multiple sheets
        // in a row.
        $this->processed = [];

        try{
            $this->progressBar = (php_sapi_name() == 'cli');  // not via web!
            $rows = PlantItemUtility::readFromSpreadsheet(
                $this->argument('file'),
                $this->option('sheet')
            );
            $this->process($rows);
            $this->newLine();
            $this->displayErrors();
            $this->newLine();
            return 0;
        }catch(\Exception $e){
            $this->newLine();
            $this->error($e->getMessage());
            $this->newLine();
            return 1;
        }

    }

    /**
     * Perform model self-validation on the models prior to
     * their insertion in the database.
     * @param $items
     * @throws \Exception
     */
    protected function process(Collection $items){
        if ($items->isEmpty()){
            throw new \Exception('Found no spreadsheet rows to process. Is the sheet number correct?');
        }
        $row = 1;
        if ($this->progressBar) {
            $bar = $this->output->createProgressBar($items->count());
            $bar->start();
        }
        foreach ($items as $item) {
            $row++;
            if (! $this->isProcessed($item['plant_id'])) {
                $plantItem = PlantItem::where('plant_id',$item['plant_id'])->first();
                if (! $plantItem){
                    throw new \Exception('plant_id '.$item['plant_id'].' not found');
                }
                // We set processed flag now rather than later so that if errors are encountered the
                // first time the plant item's isolation points are attempted, they won't get duplicated
                // by subsequent attemptes.
                $this->setProcessed($item['plant_id']);
                try{
                    DB::beginTransaction();
                    if ($this->option('replace')){
                        $plantItem->isolationPoints()->sync([]); // removes all existing
                    }
                    foreach($items->where('plant_id',$item['plant_id']) as $assignment){
                        $isolationPlantItem = PlantItem::where('plant_id',$assignment['isolation_point_plant_id'])->first();
                        if (! $isolationPlantItem){
                            throw new \Exception('isolation_point_plant_id '.$assignment['isolation_point_plant_id'].' not found');
                        }
                        if (! $plantItem->isolationPoints->contains($isolationPlantItem)){
                            $plantItem->isolationPoints()->attach($isolationPlantItem);
                        }
                    }
                    DB::commit();
                } catch (\Exception $e){
                    DB::rollBack();
                    $this->errors->add("row $row ", $e->getMessage());
                    $this->errors->add("row $row ", "plant_id {$item['plant_id']} was not updated");
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
            throw new \Exception("The file contains errors and and some rows could not be processed. Please see the errors listed above.");
        }
    }

    /**
     * Record that a plantId has been processed.
     *
     * @param $plantId
     * @return void
     */
    protected function setProcessed($plantId){
        $this->processed[$plantId] = true;
    }

    /**
     * Determine whether a plantId has already been processed.
     * @param $plantId
     * @return bool
     */
    protected function isProcessed($plantId){
        return isset($this->processed[$plantId]) && $this->processed[$plantId] == true;
    }


    /**
     * Output the contents of the errors MessageBag
     */
    protected function displayErrors(){
        foreach ($this->errors->keys() as $row){
            foreach ($this->errors->get($row) as $message){
                $this->error($message);
            }
        }
    }
}