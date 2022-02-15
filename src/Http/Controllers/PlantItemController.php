<?php

namespace Jlab\Epas\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Jlab\Epas\Exports\PlantItemExport;
use Jlab\Epas\Http\Middleware\SetPlantItemRootView;
use Jlab\Epas\Http\Resources\PlantItemCollection;
use Jlab\Epas\Http\Resources\PlantItemDetailResource;
use Jlab\Epas\Model\PlantItem;
use Jlab\Epas\Service\PlantItemSearch;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;

class PlantItemController extends Controller
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->middleware('auth')->only([
            'create', 
            'uploadPlantItems', 
            'uploadPlantItemsForm',
            'uploadIsolationPoints',
            'uploadIsolationPointsForm',
            ]);
        $this->middleware(SetPlantItemRootView::class);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        //Inertia::setRootView('jlab-epas::app');
        $itemCollection = PlantItem::where('plant_parent_id', config('epas.root_plant_item'))
            ->get()
            ->sortBy(function ($item, $key) {
                // Make parent items come first.
                return $item->hasChildren() ? 1 : 2;
            });

        $resource = new PlantItemCollection($itemCollection);
        $this->sharePlantItemFormFieldOptions();


        return Inertia::render('Pages/PlantItemPage', [
            'plantItems' => $resource->toArray(request())
        ]);
    }

    /**
     * Accept a spreadsheet upload
     */
    public function uploadPlantItems(Request $request)
    {
        try {
            // Retrieves a Laravel File Upload object
            $uploadedFile = $request->file('file');
            $fileName = $this->uniqueFileName($uploadedFile->getClientOriginalName());

            // If a file of the same name was already uploaded and
            // the user didn't explicitly ask for replacement, it's an
            // error that will abort processing.
            if ($request->get('replaceOption', false) != 'replace') {
                $this->assertFileNotAlreadyExist($fileName);
            }

            // Copy the file to the epas temp directory
            $path = $request->file('file')->storeAs(
                'epas/tmp', $fileName
            );

            // Prepare the arguments to be passed to Artisan command
            $arguments = [
                'file' => Storage::path($path),
                '--plant-group' => $request->get('plantGroup', null)
            ];

            if ($request->get('replaceOption', false) == 'update') {
                $arguments['--update'] = true;
            }

            // Here we re-use the same artisan command as via the command line
            $exitCode = Artisan::call('plant-items:upload', $arguments);
            if ($exitCode != 0) {
                // For debugging purposes, we're not deleting the
                // temp file right now when it contained errors.
                // We're simply returning the error messages to the user.
                //return $this->error(Artisan::output(), 422);
                $errors = new MessageBag(['file' => Artisan::output()]);
                return redirect(route('plant_items.upload_plant_items_form'))
                    ->withErrors($errors)
                    ->withInput();
            }

            Storage::move('epas/tmp/' . $fileName, 'epas/' . $fileName);

            // Give the user a thumbs-up
            return redirect(route('plant_items.table', ['data_source' => $fileName]))->with('success',
                'The spreadsheet was successfully uploaded.');
        } catch (\Exception $e) {
            $errors = new MessageBag(['form' => $e->getMessage()]);
            return redirect(route('plant_items.upload_plant_items_form'))
                ->withErrors($errors)
                ->withInput();
        }
    }


    /**
     * Accept a spreadsheet upload
     */
    public function uploadIsolationPoints(Request $request)
    {
//        dd($request->all());

        try {
            // Retrieves a Laravel File Upload object
            $uploadedFile = $request->file('file');
            $fileName = $this->uniqueFileName($uploadedFile->getClientOriginalName());

            // If a file of the same name was already uploaded and
            // the user didn't explicitly ask for replacement, it's an
            // error that will abort processing.
            if ($request->get('replaceOption', false) != 'replace') {
                $this->assertFileNotAlreadyExist($fileName);
            }

            // Copy the file to the epas temp directory
            $path = $request->file('file')->storeAs(
                'epas/tmp', $fileName
            );

            // Prepare the arguments to be passed to Artisan command
            $arguments = [
                'file' => Storage::path($path),
                '--sheet' => $request->get('sheet', 2)
            ];

            if ($request->get('replaceOption', false) == 'replace') {
                $arguments['--replace'] = true;
            }

            // Here we re-use the same artisan command as via the command line
            $exitCode = Artisan::call('plant-items:upload-isolation-points', $arguments);
            if ($exitCode != 0) {
                // For debugging purposes, we're not deleting the
                // temp file right now when it contained errors.
                // We're simply returning the error messages to the user.
                //return $this->error(Artisan::output(), 422);
                $errors = new MessageBag(['file' => Artisan::output()]);
                return redirect(route('plant_items.upload_isolation_points_form'))
                    ->withErrors($errors)
                    ->withInput();
            }

            Storage::move('epas/tmp/' . $fileName, 'epas/' . $fileName);

            // Give the user a thumbs-up
            return redirect(route('plant_items.upload_isolation_points_form', ['data_source' => $fileName]))->with('success', 'The spreadsheet was successfully uploaded.');
        } catch (\Exception $e) {
            $errors = new MessageBag(['form' => $e->getMessage()]);
            return redirect(route('plant_items.upload_isolation_points_form'))
                ->withErrors($errors)
                ->withInput();
        }
    }



    protected function assertFileNotAlreadyExist($file)
    {
        if (Storage::exists('epas/' . $file)) {
            throw new \Exception ('A spreadsheet data source of the same name has already been uploaded.');
        }
    }

    /**
     * Returns a filename that doesn't already exist, but is recognizable derived
     * from the original.
     *
     * @param $file
     * @return string
     */
    protected function uniqueFileName($file){
        $version = 0;
        $fileName = $file;
        while (Storage::exists('epas/' . $fileName)) {
            $version ++;
            $fileName = $this->versionedFileName($file, $version);
        }
        return $fileName;
    }

    /**
     * Obtain a file name with _$version before the suffix
     * ex: file.xlsx => file_1.xlsx
     *
     * @param $file
     * @param $version
     * @return string
     */
    protected function versionedFileName($file, $version){
        $fileParts = pathinfo($file);
        if ($version){
            return $fileParts['basename']."_$version.".$fileParts['extension'];
        }
        return $file;
    }

    protected function excel(Request $request)
    {
        return Excel::download(new PlantItemExport($request), 'items.xlsx');

    }

    protected function uploadPlantItemsForm()
    {
        return Inertia::render('Pages/PlantItemsUploadForm', [
            'formFieldData' => $this->plantItemsFormFieldData()
        ]);

    }

    protected function uploadIsolationPointsForm()
    {
        return Inertia::render('Pages/IsolationPointsUploadForm', [
            'formFieldData' => $this->isolationPointsFormFieldData()
        ]);

    }

    public function item(PlantItem $plantItem){
        $this->sharePlantItemFormFieldOptions();
        return Inertia::render('Pages/PlantItem', [
            'plantItem' => new PlantItemDetailResource($plantItem)
        ]);
    }

    public function create(Request $request){
        $this->sharePlantItemFormFieldOptions();
        $plantItem = new PlantItem();
        $this->fillWithValues($plantItem, $request->only('plantParentId'));
        return Inertia::render('Pages/PlantItemCreate', [
            'plantItem' => new PlantItemDetailResource($plantItem)
        ]);
    }

    /**
     * Sets plant item properties with values
     * @param PlantItem $plantItem
     * @param array $values
     * @return PlantItem
     */
    protected function fillWithValues(PlantItem $plantItem, array $values)
    {
        foreach ($values as $key => $value) {
            $plantItem->{Str::snake($key)} = $value;
        }
        return $plantItem;
    }

    protected function table(Request $request)
    {
        $this->shareRequest();
        $this->sharePlantItemFilters();

        // Build a search object using the request
        $search = new PlantItemSearch();
        $search->applyRequest($this->request);

        // Build a collection of Plant Items
        $models = new Collection($search->getResults());

        if ($models->count() >= $search->limit) {
            Inertia::share([
                'flash' => [
                    'warning' => "Search results have been capped at {$search->limit} items. Narrow your results with filters or choose Download"
                ]
            ]);
        }
        $resource = PlantItemDetailResource::collection($models);
        return Inertia::render('Pages/PlantItemTable', [
            'plantItems' => $resource->toArray(request()),
        ]);
    }


    protected function sharePlantItemFilters()
    {
        Inertia::share([
            'filters' => [
                'dataSourceOptions' => array_merge([0 => ''], PlantItem::dataSources()->all()),
            ]
        ]);
    }

    protected function sharePlantItemFormFieldOptions()
    {
        Inertia::share([
            'formFieldOptions' => $this->plantItemFormFieldOptions(),
        ]);
    }

    /**
     * Form element data for plant item edit/create form
     * @return array[]
     */
    protected function plantItemFormFieldOptions()
    {
        return [
            'plantGroupOptions' => $this->plantGroupOptions(),
            'methodOfProvingOptions' =>  $this->methodOfProvingOptions(),
            'circuitVoltageOptions' => $this->circuitVoltageOptions(),
        ];
    }

    /**
     * Form element data for plant item spreadsheet upload form
     * @return array[]
     */
    protected function isolationPointsFormFieldData(){
        return [
            'replaceOptions' => $this->isolationPointReplaceOptions(),
        ];
    }

    /**
     * Form element data for plant item spreadsheet upload form
     * @return array[]
     */
    protected function plantItemsFormFieldData(){
        return [
            'plantGroupOptions' => $this->spreadsheetPlantGroupOptions(),
            'replaceOptions' => $this->plantItemReplaceOptions(),
        ];
    }

    /**
     * Options for specifying plant_group when uploading a spreadsheet.
     *
     * @return array
     */
    protected function spreadsheetPlantGroupOptions(){
        $options[] = ['value' => 'spreadsheet', 'text' => 'From Spreadsheet'];
        return array_merge($options, $this->plantGroupOptions());
    }

    /**
     * Options for specifying plant_group.
     *
     * @return array
     */
    protected function plantGroupOptions()
    {
        $options = [];
        foreach (config('epas.plant_groups') as $option) {
            $options[] = ['value' => $option, 'text' => $option];
        }
        return $options;
    }

    /**
     * Options for specifying method_of_proving
     *
     * @return array
     */
    protected function methodOfProvingOptions()
    {

        $options[] = ['value' => '', 'text' => ''];
        foreach (config('epas.method_of_proving_description') as $key => $text){
            $options[] = ['value' => $key, 'text' => $text];
        }
        return $options;
    }

    /**
     * Options for specifying circuit_voltage
     *
     * @return array
     */
    protected function circuitVoltageOptions()
    {
        $options[] = ['value' => '', 'text' => ''];
        foreach (config('epas.circuit_voltage') as $key){
            $options[] = ['value' => $key, 'text' => $key];
        }
        return $options;
    }

    /**
     * Spreadsheet data source overwriting options.
     * @return \string[][]
     */
    protected function plantItemReplaceOptions()
    {
        return [
            ['value' => 'keep', 'text' => 'Do Not Update'],
            ['value' => 'update', 'text' => 'Update Plant Items']
        ];
    }

    /**
     * Spreadsheet data source overwriting options.
     * @return \string[][]
     */
    protected function isolationPointReplaceOptions()
    {
        return [
            ['value' => 'keep', 'text' => 'Preserve Existing'],
            ['value' => 'replace', 'text' => 'Replace Existing']
        ];
    }

}
