<?php


namespace Jlab\Epas\Utility;

use Jlab\Epas\Model\PlantItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Rap2hpoutre\FastExcel\FastExcel;

class PlantItemUtility
{
    /**
     * Maps headings that we expect to encounter into their acceptable
     * equivalent.  This only needed for cases where the default
     * snake_case conversion algorithm is insufficient.
     * Both the key and value of the array should be snake_case.
     * @array
     */
    protected static $headingFixes = [
        'plant_group_name' => 'plant_group'
    ];


    /**
     * Returns a collection of unsaved PlantItem models.
     *
     * @param $file
     * @param string $plant_group specifies a plant group name for all records
     *
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public static function makeFromSpreadsheet($file, $plant_group = null)
    {
        $collection = new Collection();
        foreach (static::readFromSpreadsheet($file) as $record) {
            if (static::looksLikeBlankRow($record)){
                continue;
            }
            try {
                $plantItem = new PlantItem($record);
                $plantItem->data_source = basename($file);
                if ($plant_group) {
                    $plantItem->plant_group = $plant_group;
                }
                if (isset($record['isolation_point_plant_id'])) {
                    $plantItem->isolation_point_plant_id = $record['isolation_point_plant_id'];
                }

                $collection->push($plantItem);
            } catch (\Exception $e) {
                Log::error($e->getMessage());
                Log::info($record);
                throw $e;
            }
        }
        return $collection;
    }

    /**
     * Make a collection of PlantItem models from a suitable formatted spreadsheet.
     *
     * @param $file
     * @return \Illuminate\Support\Collection
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public static function readFromSpreadsheet($file)
    {
        // We call the FastExcel import to read our file.
        // As the second argument we give it a callback function that will
        // perform some initial processing on the data that was read such as
        // trying to normalize the keys of each array record being returned
        return (new FastExcel)->sheet(1)->import($file, function ($record) {
            $processed = static::normalizeKeys($record);
            $processed = static::trimValues($processed);
            $processed = static::normalizeBooleans($processed);
            $processed = static::normalizeCircuitVoltage($processed);
            return $processed;
        });
    }

    /**
     * Answers whether the record looks like it was read from a blank row.
     * @param array $record
     * @return boolean
     */
    protected static function looksLikeBlankRow(array $record){
        return empty($record['plant_parent_d']) && empty($record['plant_id']) && empty($record['description']);
    }

    /**
     *
     * @param array $record
     * @return array
     */
    protected static function normalizeKeys(array $record)
    {
        $valid = [];
        foreach ($record as $key => $value) {
            $validKey = self::getKey(Str::snake(strtolower(trim($key))));
            if ($validKey != ''){
                $valid[$validKey] = $value;
            }
        }
        return $valid;
    }

    protected static function getKey($key){
        if (array_key_exists($key, self::$headingFixes)){
            return self::$headingFixes[$key];
        }
        return $key;
    }

    /**
     *
     * @param array $record
     * @return array
     */
    protected static function trimValues(array $record)
    {
        return array_map('trim', $record);
    }

    /**
     * Convert Yes/No and True/False to actual boolean values for
     * all is_ attributes of the model.
     * @return array
     */
    protected static function normalizeBooleans($record){
        foreach ($record as $key => $value){
            // By convention the boolean attribute names all begin with is_a
            if (substr($key,0, 3) == 'is_'){
                $record[$key] = static::makeBoolean($value);
            }
        }
        return $record;
    }

    /**
     * Convert user-entered values to valid values where possible
     *  ex: 480 => 480V
     *
     * @return array
     */
    protected static function normalizeCircuitVoltage($record){
        if (array_key_exists('circuit_voltage', $record)){
            $value = $record['circuit_voltage'];
            // Handle case where user enters voltage as an integer with 'V' on the end.
            if (is_numeric($value) && substr($value,-1,1) != 'V'){
                $record['circuit_voltage'] .= 'V';
            }
        }
        return $record;
    }

    /**
     * Convert a value to a boolean taking care to also convert
     * strings of Y/Yes/True/T to true and strings of F/False and
     * N/No to false.
     *
     * @param $value
     * @return bool
     */
    protected static function makeBoolean($value){
        if (is_string($value)){
            switch(strtoupper(substr($value,0,1))){
                case 'Y' :
                case 'T' :
                case '1' : return true;
                case 'N' :
                case 'F' : return false;
            }
        }
        return (bool) $value;
    }

}
