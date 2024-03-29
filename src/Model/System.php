<?php

namespace Jlab\Epas\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class System extends Model {

    protected $table = 'srm_owner.system';

    protected $primaryKey = 'system_id';

    public function category(): BelongsTo {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function components(): HasMany {
        return $this->hasMany(Component::class, 'system_id', 'system_id');
    }

    public function componentRegions(): Collection {
        return $this->components()->get()->map(function($item) {
            return $item->region;
        })->unique()->sortBy('weight');
    }

    public function hasEpas(): bool {
        return DB::table('srm_owner.system_application')
            ->leftJoin('srm_owner.application', 'srm_owner.system_application.application_id', '=', 'application.application_id')
            ->where('system_id', $this->system_id)
            ->where('name', 'ePAS')
            ->exists();
    }

    public function plantItem(Region $region): ?PlantItem {
        return PlantItem::where('plant_id', $this->plantId($region))->first();
    }

    public function hasPlantItem(Region $region): bool {
        return $this->plantItem($region) !== NULL;
    }

    public function plantParentId(Region $region): string {
        return $region->archi_loc;
    }

    public function plantId(Region $region): string {
        return $this->plantParentId($region) . "-HCO_SYSTEM_ID-{$this->system_id}";
    }

}
