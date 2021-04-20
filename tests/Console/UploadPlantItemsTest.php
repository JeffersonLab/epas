<?php

namespace Jlab\Epas\Tests\Console;

use Jlab\Epas\Model\PlantItem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Jlab\Epas\Tests\TestCase;

class UploadPlantItemsTest extends TestCase
{

    // A test case containing building 58 data
    protected $testFile1 = __DIR__.'/../_data/MainMachineShopEdited.xlsx';
    protected $testFile2 = __DIR__.'/../_data/MainMachineShopEditedUpdated.xlsx';

    public function setUp(): void
    {
        parent::setUp();
        DB::table('plant_items')->insert([
            'plant_id' => 'FM-L-90_1',
            'description' => 'Experimental Equipment Lab (EEL) Floor 1',
            'data_source' => 'testing',
            'plant_group' => 'Facilities',
        ]);
        DB::table('plant_items')->insert([
            'plant_id' => 'FM-L-90_1.101',
            'plant_parent_id' => 'FM-L-90_1',
            'description' => 'Experimental Equipment Lab (EEL) Floor 1',
            'data_source' => 'testing',
            'plant_group' => 'Facilities',
        ]);
    }

    public function test_it_loads_spreadsheet_with_isolations(){
        // Assert that the prerequisites exist
        $item = PlantItem::where('plant_id', 'FM-L-90_1.101')->first();
        $this->assertEquals('Experimental Equipment Lab (EEL) Floor 1', $item->description);

        // Call the command and test its exit code
        $this->artisan('plant-items:upload',[
            'file' => $this->testFile1,
            '--plant-group' => 'Facilities',
        ])
        ->assertExitCode(0);

        // Finally spot check for expected data
        $found = PlantItem::where('plant_id',strtoupper('FM-L-90_1.101 Haas CNC Milling Machine'))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertEquals('FM-L-90_1.101 HAAS CNC MILLING MACHINE DISC.', $found->isolationPoints->first()->plant_id);

    }

    public function test_it_uploads_and_then_revises_spreadsheet(){
        // Repeat the previous test to reach a starting point
        $this->test_it_loads_spreadsheet_with_isolations();

        $original = PlantItem::where('plant_id',strtoupper('FM-L-90_1.101 Haas CNC Milling Machine'))->first();
        $this->assertEquals('Haas CNC Milling Machine', $original->description);
        $this->assertEmpty( $original->location);

        // Call the command and test its exit code
        $this->artisan('plant-items:upload',[
            'file' => $this->testFile2,
            '--plant-group' => 'Facilities',
            '--update' => true,
        ])
        ->assertExitCode(0);

        $updated = PlantItem::where('plant_id',strtoupper('FM-L-90_1.101 Haas CNC Milling Machine'))->first();
        $this->assertEquals('Haas CNC Milling Machine Updated', $updated->description);
        $this->assertEquals('Updated Location', $updated->location);
        $this->assertEquals('FM-L-90_1.101 HAAS CNC MILLING MACHINE DISC.', $updated->isolationPoints->first()->plant_id);

    }

}
