<?php

namespace Jlab\Epas\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model;
use Jlab\Epas\Model\PlantItem;

class PlantItemPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the model.
     *
     * @param Model $user
     * @param Model $model
     * @return mixed
     */
    public function view(Model $user, Model $model)
    {
        return true;
    }

    /**
     * Determine whether the user can create tasks.
     *
     * @param  Model $user
     * @return boolean
     */
    public function create(Model $user)
    {
        return ($user->isAdmin() || $this->isPlantItemAdmin($user));
        // @TODO relax to any authenticated user
        // return $user && $user->id > 0;
    }


    /**
     * Determine whether the user can create tasks.
     *
     * @param  Model  $user
     * @return boolean
     */
    public function update(Model $user, PlantItem $plantItem)
    {
        // Even admins aren't allowed to edit items from external data sources
        if ($plantItem->isFromExternalDataSource()){
            return false;
        }
        // Otherwise any plant item is fair game.
        return $user->isAdmin() || $this->isPlantItemAdmin($user);

        //@TODO relax to permit owner(s)
    }

    /**
     * Determine whether the user can create tasks.
     *
     * @param  Model  $user
     * @return boolean
     */
    public function delete(Model $user, PlantItem $plantItem)
    {
        // Even admins aren't allowed to delete items from external data sources
        if ($plantItem->isFromExternalDataSource()){
            return false;
        }
        // Not allowed to delete parent items b/c of foreign key constraints
        if ($plantItem->hasChildren()) {
            return false;
        }
        return $user->isAdmin() || $this->isPlantItemAdmin($user);

    }


    /**
     * Check whether the user is in the config file as a plant item admin.
     * @param Model $user
     * @return bool
     */
    protected function isPlantItemAdmin(Model $user){
        return in_array($user->username, config('epas.admins',[]));
    }


}
