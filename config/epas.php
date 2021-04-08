<?php
return [

    /*
    |--------------------------------------------------------------------------
    | ePAS Administrators
    |--------------------------------------------------------------------------
    |
    | Here you specify the list of usernames of the individuals with privilege to
    | administer plant item content.
    |
    */
    'admins' => [],

    /*
    |--------------------------------------------------------------------------
    | ePAS Administrators
    |--------------------------------------------------------------------------
    |
    | Here you specify the list of data source names that indicate the data has
    | an authoritative source external to this application.  The ability to edit
    | items from these data sources may be curtailed.
    |
    */
    'external_data_sources' => ['HCO','maximo.db'],

    /*
    |--------------------------------------------------------------------------
    | ePAS Methods of Proving
    |--------------------------------------------------------------------------
    |
    | Here you specify the list of valid method_of_proving attribute values
    |
    | ZEV - zero energy verification
    | ZVV - zero voltage verification
    | VVU - voltage verification unit
    */
    'method_of_proving' => ['ZEV', 'ZVV', 'VVU'],

    // With descriptions added for use in form drop-downs
    'method_of_proving_description' => [
        'ZEV' => 'Zero Energy Verification',
        'ZVV' => 'Zero Voltage Verification',
        'VVU' => 'Voltage Verification Unit'
    ],

    /*
    |--------------------------------------------------------------------------
    | ePAS circuit voltage
    |--------------------------------------------------------------------------
    |
    | Here you specify the list of valid circuit_voltage attribute values
    |
    |
    */
    'circuit_voltage' => ['120V', '208V', '277V','480V','4.16kV','13kV'],

    /*
    |--------------------------------------------------------------------------
    | ePAS Plant Groups
    |--------------------------------------------------------------------------
    |
    | Here you specify the list of valid Plant Groups that exist in ePAS
    |
    */
    'plant_groups' => ['Accelerator', 'Engineering', 'Facilities', 'Physics']


];
