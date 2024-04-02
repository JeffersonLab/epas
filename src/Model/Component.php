<?php

namespace Jlab\Epas\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An HCO component for import into ePAS
 */
class Component extends Model {

    protected $table = 'srm_owner.component';

    protected $primaryKey = 'component_id';

    public function system(): BelongsTo {
        return $this->belongsTo(System::class, 'system_id', 'system_id');
    }

    public function region(): BelongsTo {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function plantItem(): ?PlantItem {
        return PlantItem::where('plant_id', $this->plantId())->first();
    }

    public function hasPlantItem(): bool {
        return $this->plantItem() !== NULL;
    }

    public function flatRegionPlantParentId() : string {
        return $this->region->archi_loc;
    }

    public function regionSystemPlantParentId() : string {
        return $this->system->plantId($this->region);
    }

    public function plantParentId() : string {
        return $this->flatRegionPlantParentId();
    }

    public function plantId(): string {
        return "HCO_COMPONENT_ID-{$this->component_id}";
    }

    public function plantGroup() : string {
        $facility = $this->system->category->facilityName();
        switch ($facility) {
            case 'Hall A'   :
            case 'Hall B'   :
            case 'Hall C'   :
            case 'Hall D'   : return 'Physics';
            case 'Cryo'     : return 'Engineering';
            case 'Facilities' :return 'Facilities';
            case 'LERF'     :
            case 'UITF'     :
            case 'CEBAF'    :
            default         :    return 'Accelerator';
        }
    }

    public function toPlantItem() : PlantItem{
        $attributes = [
            'plant_id' => $this->plantId(),
            'plant_parent_id' => $this->plantParentId(),
            'description' => $this->name,
            'location' => $this->region->archi_loc ?: $this->region->name,
            'plant_group' => $this->plantGroup(),
            'plant_type' => $this->system->name,
            'is_plant_item' => 1,
            'data_source' => 'HCO',
        ];
        return new PlantItem($attributes);
    }

}
