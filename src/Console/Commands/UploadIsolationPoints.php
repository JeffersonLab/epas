<?php

namespace Jlab\Epas\Console\Commands;

use Illuminate\Console\Command;
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
            $rows = PlantItemUtility::readFromSpreadsheet(
                $this->argument('file'),
                $this->option('sheet')
            );
            $this->withProgressBar($rows, function ($row) {
                $this->process($row);
            });
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
     * Processes rows of data each of which is expected to be a 2D associative array with the keys
     * plant_id and isolation_plant_point_id.
     *
     * Rows containing invalid data will generate an error message and be skipped.
     * Attempts to re-add same isolation point to a plant item will be ignored without error messages.
     *
     * @param array $row
     * @return bool true if no errors
     */
    protected function process(array $row){
        try{
            $plantItem = PlantItem::where('plant_id',$row['plant_id'])->first();
            if (! $plantItem){
                throw new \Exception('plant_id '.$row['plant_id'].' not found');
            }
            $isolationPlantItem = PlantItem::where('plant_id',$row['isolation_point_plant_id'])->first();
            if (! $isolationPlantItem){
                throw new \Exception('isolation_point_plant_id '.$row['isolation_point_plant_id'].' not found');
            }
            // TODO transaction to remove and replace based on a --replace flag
            if (! $plantItem->isolationPoints->contains($isolationPlantItem)){
                $plantItem->isolationPoints()->attach($isolationPlantItem);
            }
            return true;
        }catch(\Exception $e){
            $this->errors->add("error", $e->getMessage());
            return false;
        }
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