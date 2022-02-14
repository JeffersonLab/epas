<?php
namespace Jlab\Epas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Jlab\Epas\Exception\ModelException;
use Jlab\Epas\Http\Resources\PlantItemDetailResource;
use Jlab\Epas\Http\Resources\PlantItemResource;
use Jlab\Epas\Model\PlantItem;
use Jlab\Epas\Service\PlantItemSearch;
use PDOException;

class PlantItemApiController extends ApiController
{

    /**
     * Retrieve a list of plant items that are children of a specified plant_parent_id.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function children(Request $request)
    {
        $children = PlantItem::where('plant_parent_id', $request->get('plant_parent_id'))->get();
        return $this->resourceResponse(PlantItemResource::collection($children));
    }

    /**
     * Retrieve a PlantItem.
     *
     * @param PlantItem $plantItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function item(PlantItem $plantItem)
    {
        return $this->resourceResponse(new PlantItemDetailResource($plantItem));
    }

    /**
     * Retrieve a list of plant items that are isolation points and that match
     * the request query parameters.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function isolationPoints(Request $request)
    {
        // Build a search object using the request
        $search = new PlantItemSearch();

        // Because it's the purpose of this method, we force
        // the isIsolationPoint filter to be used.
        $request->query->set('isIsolationPoint', true);

        // The primary query parameter we implicitly expect is
        // being passed to applyRequest is "IsolationPointLike"
        $search->applyRequest($request);

        // Build a collection of Plant Items
        $models = new Collection($search->getResults());

        return $this->resourceResponse(PlantItemResource::collection($models));
    }

    /**
     * Return a list of Plant items that match the provided query request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Build a search object using the request
        $search = new PlantItemSearch();
        $search->applyRequest($request);

        // Build a collection of Plant Items
        $models = new Collection($search->getResults());

        return $this->resourceResponse(PlantItemResource::collection($models));
    }

    /**
     * Store a new Plant Item in the database.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request)
    {
        $this->authorize('create', PlantItem::class);
        try {
            $plantItem = new PlantItem();
            $this->fillWithValues($plantItem, $request->except([
                'createdAt',
                'updatedAt',
                'dataSource',
                'dataSourceId',
                'integratedAt',
                'isolationPoints',
                'can'
            ]));
            $plantItem->data_source = 'WEB';
            $this->validateAndSave($plantItem);
            if ($request->has('isolationPoints')) {
                $this->setIsolationPoints($plantItem, $request->get('isolationPoints'));
            }
            return $this->resourceResponse(new PlantItemDetailResource($plantItem));
        } catch (ModelException $e) {
            return $this->error($e->getMessage(), 422, $e->getModel()->errors());
        } catch (PDOException $e) {
            return $this->databaseErrorResponse($e);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Sets fillable plant item properties with values from an array.
     *
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

    /**
     * Save the plant item if it passes validation checks, otherwise throw a ModelException.
     *
     * @param PlantItem $plantItem
     * @return PlantItem|null
     * @throws ModelException
     */
    protected function validateAndSave(PlantItem $plantItem)
    {
        // save() will call validate automatically.
        if (!$plantItem->save()) {
            throw new ModelException('Failed to save Plant Item', $plantItem);
        }

        // Return with data refreshed from DB
        // (i.e with latest timestamps, etc.)
        return $plantItem->fresh();
    }

    /**
     * Assign isolation points to a plant item.
     *
     * @param PlantItem $plantItem
     * @param array $isolationPoints
     * @return void
     */
    protected function setIsolationPoints(PlantItem $plantItem, $isolationPoints)
    {
        $collection = collect($isolationPoints);
        $plantItem->isolationPoints()->sync($collection->pluck('id'));
    }

    /**
     * Provides more user-friendly database error messages when possible
     *
     * @param PDOException $e
     * @return mixed
     */
    protected function databaseErrorResponse(PDOException $e)
    {
        Log::error($e);
        switch ($e->getCode()) {
            case    1 :  // Unique Constraint must be plant_id
                return $this->error('Plant Id already exists', 422);
            case 2291 :  // Integrity constraint must be plant_parent_id
                return $this->databaseForeignKeyErrorResponse($e);
        }
        return $this->error('Database Error', 422);
    }

    /**
     * Provides more user-friendly foreign key error messages when possible
     *
     * @param PDOException $e
     * @return mixed
     */
    protected function databaseForeignKeyErrorResponse(PDOException $e)
    {
        if (stristr($e->getMessage(), 'ISOLATION_PLANT_ITEM_ID')) {
            return $this->error('Unable to associate invalid Isolation Point', 422);
        }
        if (stristr($e->getMessage(), 'PLANT_PARENT_ID')) {
            return $this->error('Invalid value for Plant Parent Id', 422);
        }
        return $this->error('Save rejected by Database Foreign Key Constraint', 422);
    }

    /**
     * Update the fillable attributes of a PlantItem.
     *
     * @param PlantItem $plantItem
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(PlantItem $plantItem, Request $request)
    {
        $this->authorize('update', $plantItem);
        try {

            // An entire plantItem was probably sent including values that,
            // shouldn't be changed via update.  We exclude those values from
            // being set.
            $this->fillWithValues($plantItem, $request->except([
                'plantId',
                'createdAt',
                'updatedAt',
                'dataSource',
                'dataSourceId',
                'integratedAt',
                'isolationPoints',
                'can'
            ]));

            $this->validateAndSave($plantItem);
            if ($request->has('isolationPoints')) {
                $this->setIsolationPoints($plantItem, $request->get('isolationPoints'));
            }
            return $this->resourceResponse(new PlantItemDetailResource($plantItem));
        } catch (ModelException $e) {
            return $this->error($e->getMessage(), 422, $e->getModel()->errors());
        } catch (PDOException $e) {
            return $this->databaseErrorResponse($e);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Delete a plant item from the database.
     *
     * @param PlantItem $plantItem
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(PlantItem $plantItem, Request $request)
    {
        $this->authorize('delete', $plantItem);
        try {
            $plantItem->delete();
            return $this->resourceResponse(new PlantItemDetailResource(new PlantItem()));
        } catch (ModelException $e) {
            return $this->error($e->getMessage(), 422, $e->getModel()->errors());
        } catch (PDOException $e) {
            return $this->databaseErrorResponse($e);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
}
