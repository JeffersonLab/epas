<?php

namespace Jlab\Epas\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Jlab\Epas\Exception\CategoryException;


class Category extends Model {

    protected $table = 'srm_owner.category';
    protected $primaryKey = 'category_id';

    /**
     * Get the systems belonging to the category.
     *
     * By default, the systems belonging to subcategories are also included.
     *
     */
    public function systems(bool $withSubCategories = true) : Collection {
        $systems = $this->hasMany(System::class, 'category_id', 'category_id')->get();
        if (! $withSubCategories  || ! $this->hasSubCategories()){
            return $systems;
        }
        foreach ($this->subCategories as $category){
            $category->systems($withSubCategories)->each(function($item) use($systems) {
               $systems->push($item);
            });
        }
        return $systems;
    }

    public function hasSubCategories() {
        return $this->subCategories()->exists();
    }

    public function subCategories() : hasMany {
        return $this->hasMany(Category::class, 'parent_id', 'category_id');
    }

    public function parentCategory() : BelongsTo {
        return $this->belongsTo(Category::class, 'parent_id', 'category_id');
    }

    /**
     * The name of the uppermost parent category whose own parent is JLAB
     *
     * @throws \Jlab\Epas\Exception\CategoryException
     */
    public function facilityName() : string {
        if ($this->parent_id === null){
            throw new CategoryException('Cannot retrieve facility name for null parent_id');
        }
        if ($this->parentCategory->name == 'JLAB'){
            return $this->name;
        }
        return $this->parentCategory->facilityName();
    }

    public function hasParentCategory() : bool {
        return $this->parentCategory() !== null;
    }

    public function plantId() : string {
        return "HCO_CATEGORY_ID-{$this->category_id}";
    }
}
