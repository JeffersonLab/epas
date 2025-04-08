<?php

namespace Jlab\Epas\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * An HCO component for import into ePAS
 */
class Component extends Model {

    protected $table = 'srm_owner.component';

    protected $primaryKey = 'component_id';

    // The attributes we care most about when syncing components to plant items
    public $attributesOfConcern = [
        'description',
    //    'location',
        'plant_group',
        'plant_type',
    //    'plant_parent_id',
    ];

    public function system(): BelongsTo {
        return $this->belongsTo(System::class, 'system_id', 'system_id');
    }

    public function region(): BelongsTo {
        return $this->belongsTo(Region::class, 'region_id', 'region_id');
    }

    public function plantItem(): ?PlantItem {
        return PlantItem::where('plant_id', $this->plantId())->first();
    }

    public static function findByPlantId(string $plantId): ?Component {
        $componentId = str_replace("HCO_COMPONENT_ID-", '', $plantId);
        return Component::where('component_id', $componentId)->first();
    }

    public function existsInDatabase(): bool {
        return PlantItem::where('plant_id', $this->plantId())->exists();
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
            'description' => $this->description(),
            'location' => $this->region->name,
            'plant_group' => $this->plantGroup(),
            'plant_type' => $this->system->name,
            'is_plant_item' => 1,
            'data_source' => 'HCO',
        ];
        return new PlantItem($attributes);
    }

    public function description() {
        return "{$this->name} - {$this->epasSystemName()}";
    }

    public function epasSystemName(): string {
        if (array_key_exists($this->system->name, config('epas.system_renames'))) {
            return config('epas.system_renames')[$this->system->name];
        }
        return $this->system->name;
    }

    public function matchesExistingPlantItem() {
        return $this->existsInDatabase() &&  $this->matches($this->plantItem());
    }

    // Does this
    public function matches(PlantItem $plantItem) : bool {
        return $this->toPlantItem()->only($this->attributesOfConcern) == $plantItem->only($this->attributesOfConcern);
    }

    public function getMismatchedAttributes() : array {
        $hcoAttributes = $this->toPlantItem()->only($this->attributesOfConcern);
        $epasAttributes = $this->plantItem()->only($this->attributesOfConcern);
        $difference = [];
        foreach ($hcoAttributes as $key => $value) {
            if ($epasAttributes[$key] != $value) {
                $difference[$key] = "{$epasAttributes[$key]} / {$value}";
            }
        }
        return $difference;
    }

    //
    public function location() {

    }

}
