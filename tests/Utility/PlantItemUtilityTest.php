<?php


namespace Jlab\Epas\Tests\Utility;


use Jlab\Epas\Utility\PlantItemUtility;
use Jlab\Epas\Tests\TestCase;

class PlantItemUtilityTest extends TestCase
{
    // A test case containing building 58 data
    // It contains errors such as blank rows and missing descriptions
    protected $testFile1 = __DIR__.'/../_data/test_case_1.xlsx';

    function test_it_reads_test_file_1(){
        $items = PlantItemUtility::readFromSpreadsheet($this->testFile1);
        $this->assertEquals($items->first()['plant_id'],'FM-58_1-1019-150 Ton Press Disc.');
        $this->assertEquals($items->first()['plant_parent_id'],'FM-58_1-1019');
        $this->assertEquals($items->first()['upstream_isolation'],'FM-58-DP-LAB-1/6');
        $this->assertEquals($items->first()['description'],'Disconnect for 150 Ton Press');
        $this->assertEquals($items->first()['location'], '55_1-1019');
    }

    function test_it_makes_models_from_test_file_1(){
        $models = PlantItemUtility::makeFromSpreadsheet($this->testFile1, 'Accelerator');
        $this->assertCount(59, $models);
    }

    function test_it_validates_test_file_1_properly(){
        $models = PlantItemUtility::makeFromSpreadsheet($this->testFile1, 'Accelerator');
        // We know certain items from the spreadsheet are good or bad and make the appropriate assertions
        $this->assertTrue($models->get(0)->validate());  // row 2 of spreadsheet, right below header
        $this->assertFalse($models->get(12)->validate());  // row 14 of spreadsheet, an empty row
        $this->assertFalse($models->get(17)->validate());  // row 20 of spreadsheet, missing description
    }
}
