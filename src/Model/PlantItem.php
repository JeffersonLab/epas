<?php

namespace Jlab\Epas\Model;

use ElasticScoutDriverPlus\CustomSearch;
use Illuminate\Support\Facades\DB;
use Jlab\LaravelUtilities\BaseModel;
use Laravel\Scout\Searchable;

/**
 * Class PlantItem
 */
class PlantItem extends BaseModel
{

    use Searchable;
    use CustomSearch;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime:Y-m-d H:i',
        'updated_at' => 'datetime:Y-m-d H:i',
        'integrated_at' => 'datetime:Y-m-d H:i',
        'is_isolation_point' => 'boolean',
        'is_plant_item' => 'boolean',
        'is_confined_space' => 'boolean',
        'is_safety_item' => 'boolean',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'integrated_at',
    ];

    /**
     * The plant_id of an isolation point.
     * It is only used during import via spreadsheet
     * when it might exist as a reference to another item's plant_id.
     *
     * @var string
     */
    public $isolation_point_plant_id;

    /**
     * Only attributes listed here may be mass-assigned.
     * Enumerating the valid attributes here will help prevent data
     * loaded from spreadsheets with unwanted columns from getting
     * set as attributes and then generating database errors when
     * inserted.
     *
     * @var string[]
     */
    protected $fillable = [
        'plant_parent_id',
        'plant_id',
        'functional_location',
        'parent_functional_location',
        'asset_management_id',
        'description',
        'location',
        'code',
        'plant_group',
        'plant_type',
        'default_restore_condition',
        'default_isolation_condition',
        'is_plant_item',
        'is_isolation_point',
        'is_confined_space',
        'is_safety_system',
        'barcode_id',
        'equipment_num',
        'is_temporary_item',
        'plant_identifier',
        'isolation_point_num',
        'switchboard_cubicle_number',
        'circuit_voltage',
        'is_critical_plant',
        'drawing_reference',
        'is_limited_authority',
        'method_of_proving',
        'is_passing_valve',
        'data_source',
        'data_source_id',
    ];


    /**
     * Validation rules
     * @see http://laravel.com/docs/5.0/validation
     * @var array
     */
    public static $rules = [
        'id' => 'integer',
        'integrated_at' => 'date|nullable',
        'plant_parent_id' => 'required|max:255',  // only top-levels from Maximo may be null
        'plant_id' => 'required|max:255',
        'functional_location' => 'max:255',
        'parent_functional_location' => 'max:255',
        'asset_management_id' => 'max:100',
        'description' => 'required | max:500',
        'location' => 'max:100',
        'code' => 'max:500',
        'plant_group' => 'required | max:255 | inConfig:epas.plant_groups',
        'plant_type' => 'max:255',
        'data_source' => 'required | max:255',
        'default_restore_condition' => 'max:200',
        'default_isolation_condition' => 'max:200',
        'is_plant_item' => 'nullable|boolean',
        'is_isolation_point' => 'nullable|boolean',
        'is_confined_space' => 'nullable|boolean',
        'is_safety_system' => 'nullable|boolean',
        'barcode_id' => 'max:50',
        'equipment_num' => 'max:100',
        'is_temporary_item' => 'nullable|boolean',
        'plant_identifier' => 'max:100',
        'isolation_point_num' => 'max:100',
        'switchboard_cubicle_number' => 'max:255',
        'circuit_voltage' => 'nullable|max:50|inConfig:epas.circuit_voltage',
        'is_critical_plant' => 'nullable|boolean',
        'drawing_reference' => 'max:100',
        'is_limited_authority' => 'nullable|boolean',
        'method_of_proving' => 'nullable|max:255|inConfig:epas.method_of_proving',
        'is_passing_valve' => 'nullable|boolean',
        'data_source_id' => 'max:255',
];

    /**
     * Child Plant Items relationship.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children(){
        return $this->hasMany(PlantItem::class, 'plant_parent_id','plant_id');
    }

    /**
     * Parent Plant Item relationship.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function parent(){
        return $this->belongsTo(PlantItem::class, 'plant_parent_id','plant_id');
    }

    /**
     * Related Isolation Point Plant Items relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function isolationPoints(){
        return $this->belongsToMany(PlantItem::class, 'isolation_points', 'plant_item_id', 'isolation_plant_item_id');
    }


    /**
     * @return bool
     */
    public function hasChildren(){
        return $this->children()->exists();
    }

    /**
     * @return bool
     */
    public function hasParent(){
        return $this->parent()->exists();
    }

    /**
     * @return bool
     */
    public function hasIsolationPoints(){
        return $this->isolationPoints()->exists();
    }

    /**
     * Ensures that the plant_id attribute gets saved uppercase.
     * @param $value
     */
    public function setPlantIdAttribute($value){
        $this->attributes['plant_id'] = strtoupper($value);
    }

    /**
     * Ensures that the plant_parent_id attribute gets saved uppercase.
     * @param $value
     */
    public function setPlantParentIdAttribute($value){
        $this->attributes['plant_parent_id'] = strtoupper($value);
    }

    /**
     * Mutator that allows the user to specify a voltage and have it
     * converted into appropriate name.
     *
     *  Name    Circuit Voltage
     *    120V     120
     *    208V     240
     *    277V     277
     *    480V     480
     *  4.16kV    4160
     *    13kV   13000
     *
     * @param $value
     */
    public function setCircuitVoltageAttribute($value){
        switch($value){
            case 120   : $name = '120V'; break;
            case 240   : $name = '208V'; break;
            case 277   : $name = '277V'; break;
            case 480   : $name = '480V'; break;
            case 4160  : $name = '4.16kV'; break;
            case 13000 : $name = '13kV'; break;
            default    : $name = $value;
        }
        $this->attributes['circuit_voltage'] = $name;
    }

    /**
     * @return bool
     */
    public function isFromExternalDataSource(){
        return in_array($this->data_source, config('epas.external_data_sources'));
    }

    /**
     * Returns the list of data_source values that exist in the database
     */
    public static function dataSources(){
        return DB::table('plant_items')
            ->select('data_source')->distinct()
            ->pluck('data_source');
    }
}
