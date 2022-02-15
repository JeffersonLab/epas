<?php

namespace Jlab\Epas\Tests\Console;

use Jlab\Epas\Model\PlantItem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Jlab\Epas\Tests\TestCase;

class UploadIsolationPointsTest extends TestCase
{

    // A test case containing building 58 data
    protected $testFile1 = __DIR__.'/../_data/Bldg23.xlsx';
    protected $testFile2 = __DIR__.'/../_data/Bldg23Updated.xlsx';


    public function setUp(): void
    {
        parent::setUp();
        DB::table('plant_items')->insert([
            'plant_id' => 'FM-L-23_1',
            'description' => 'Building 23 Floor 1',
            'data_source' => 'testing',
            'plant_group' => 'Facilities',
        ]);
        DB::table('plant_items')->insert([
            'plant_id' => 'FM-A-23-HA',
            'plant_parent_id' => 'FM-L-23_1',
            'description' => 'Panel 23-HA',
            'data_source' => 'testing',
            'plant_group' => 'Facilities',
        ]);
        DB::table('plant_items')->insert([
            'plant_id' => 'FM-A-23-LA',
            'plant_parent_id' => 'FM-L-23_1',
            'description' => 'Panel 23-LA',
            'data_source' => 'testing',
            'plant_group' => 'Facilities',
        ]);
        DB::table('plant_items')->insert([
            'plant_id' => 'FM-A-23-MDP',
            'plant_parent_id' => 'FM-L-23_1',
            'description' => 'Panel 23-MDP',
            'data_source' => 'testing',
            'plant_group' => 'Facilities',
        ]);
        DB::table('plant_items')->insert([
            'plant_id' => 'FM-A-49-E3-SB',
            'plant_parent_id' => 'FM-L-23_1',
            'description' => 'Panel 49-E3-SB',
            'data_source' => 'testing',
            'plant_group' => 'Facilities',
        ]);

    }

    public function test_it_sets_isolation_points(){
        // Assert that at least one prerequisite exists
        $item = PlantItem::where('plant_id', 'FM-A-23-HA')->first();
        $this->assertEquals('Panel 23-HA', $item->description);

        // Call the command and test its exit code
        $this->artisan('plant-items:upload-isolation-points',[
            'file' => $this->testFile1,
            '--sheet' => '2',
        ])
        ->assertExitCode(0);

        // No verify that isolation points where assigned
        $found = PlantItem::where('plant_id',strtoupper('FM-A-23-HA'))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertEquals('FM-A-23-MDP', $found->isolationPoints->first()->plant_id);

        $found = PlantItem::where('plant_id',strtoupper('FM-A-23-LA'))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertEquals('FM-A-23-MDP', $found->isolationPoints->first()->plant_id);

        $found = PlantItem::where('plant_id',strtoupper('FM-A-23-MDP'))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertEquals('FM-A-49-E3-SB', $found->isolationPoints->first()->plant_id);
    }


    public function test_it_keeps_and_adds_isolation_points(){
        // Assert that at least one prerequisite exists
        $item = PlantItem::where('plant_id', 'FM-A-23-HA')->first();
        $this->assertEquals('Panel 23-HA', $item->description);

        // Call the command and test its exit code
        $this->artisan('plant-items:upload-isolation-points',[
            'file' => $this->testFile1,
            '--sheet' => '2',
        ])
        ->assertExitCode(0);


        // Verify that isolation points where assigned
        foreach (['FM-A-23-HA','FM-A-23-LA','FM-A-23-MDP'] as $plantId)
        $found = PlantItem::where('plant_id',strtoupper($plantId))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertCount(1, $found->isolationPoints()->get());

        // Load the second spreadsheet without specifying replace which should
        //  -  add a second isolation point to HA and LA
        //  -  re-assign existing isolation point to MDP.
        $this->artisan('plant-items:upload-isolation-points',[
            'file' => $this->testFile2,
            '--sheet' => '2',
        ])
        ->assertExitCode(0);


        foreach (['FM-A-23-HA','FM-A-23-LA'] as $plantId)
        $found = PlantItem::where('plant_id',strtoupper($plantId))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertCount(2, $found->isolationPoints()->get());

        $found = PlantItem::where('plant_id',strtoupper('FM-A-23-MDP'))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertCount(1, $found->isolationPoints()->get());

        // Load the second spreadsheet this time specifying replace which should
        //  - leave HA and LA with a single isolation point
        //  - re-assign existing isolation point to MDP.
        //  - make all three have the same single isolation point
        $this->artisan('plant-items:upload-isolation-points',[
            'file' => $this->testFile2,
            '--sheet' => '2',
            '--replace' => true,
        ])
        ->assertExitCode(0);

        foreach (['FM-A-23-HA','FM-A-23-LA','FM-A-23-MDP'] as $plantId)
        $found = PlantItem::where('plant_id',strtoupper($plantId))->first();
        $this->assertNotEmpty($found);
        $this->assertTrue($found->hasIsolationPoints());
        $this->assertCount(1, $found->isolationPoints()->get());
        $this->assertEquals('FM-A-49-E3-SB', $found->isolationPoints()->first()->plant_id);

    }

    public function test_it_exits_non_zero()
    {

        // Call the command and test its exit code.
        // Because we're telling it to parse an empty sheet, we expect that to raise an error
        $this->artisan('plant-items:upload-isolation-points', [
            'file' => $this->testFile1,
            '--sheet' => '1',
        ])
            ->assertExitCode(1);
    }
}
