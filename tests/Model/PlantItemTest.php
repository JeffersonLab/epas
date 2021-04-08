<?php

namespace Jlab\Epas\Tests\Model;

use Illuminate\Support\Facades\DB;
use Jlab\Epas\Tests\TestCase;

class PlantItemTest extends TestCase
{
    protected $globalParent;

    public function setUp(): void
    {
        parent::setUp();

        $this->globalParent = factory(\Jlab\Epas\Model\PlantItem::class)->create();
        $this->globalParent->quickSave();

    }

    function test_it_validates_valid_items(){
        $item = factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_parent_id' => $this->globalParent->plant_id
        ]);
        $this->assertTrue($item->validate());
    }

    function test_it_validates_required_fields(){
        $item = factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_parent_id' => $this->globalParent->plant_id
        ]);
        $this->assertTrue($item->validate());
        foreach (['plant_id','description','plant_group','data_source'] as $field){
            $itemCopy = clone $item;
            $this->assertTrue($itemCopy->validate());
            $itemCopy->{$field} = null;
            $this->assertFalse($itemCopy->validate());
        }
    }

    function test_it_uppercases_plant_id(){
        $item1 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi1',
        ]);
        $item1->fresh();
        $this->assertEquals('PI1', $item1->plant_id);
    }

    function test_it_has_parent_or_child_or_not(){
        $item1 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi1',
            'plant_parent_id' => 'zzz'
        ]);
        $item2 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi2',
            'plant_parent_id' => 'pi1'
        ]);
        $this->assertTrue($item2->hasParent());
        $this->assertFalse($item2->hasChildren());
        $this->assertFalse($item1->hasParent());  //we never created zzz!
        $this->assertTrue($item1->hasChildren());
    }

    function test_it_fetches_isolation_points(){
        $item1 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi1',
        ]);
        $item1->quickSave(); // skip validation of null plant_parent_id.

        $this->assertFalse($item1->hasIsolationPoints());
        $item2 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi2',
        ]);
        $item2->quickSave(); // skip validation of null plant_parent_id.

        DB::table('isolation_points')->insert([
            'plant_item_id' => $item1->id,
            'isolation_plant_item_id' => $item2->id,
        ]);
        $this->assertEquals('PI2', $item1->isolationPoints->first()->plant_id);
        $this->assertTrue($item1->hasIsolationPoints());
    }

    function test_it_attaches_and_detaches_isolation_points(){
        $item1 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi1',
        ]);
        $item1->quickSave(); // skip validation of null plant_parent_id.
        $this->assertFalse($item1->hasIsolationPoints());
        $item2 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi2',
        ]);
        $item2->quickSave(); // skip validation of null plant_parent_id.
        $item1->isolationPoints()->attach($item2);
        $this->assertEquals('PI2', $item1->isolationPoints->first()->plant_id);
        $this->assertTrue($item1->hasIsolationPoints());

        $item1->isolationPoints()->detach($item2);
        $this->assertFalse($item1->hasIsolationPoints());
    }

    function test_it_validates_circuit_voltage(){
        $item1 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi1',
            'plant_parent_id' => $this->globalParent->plant_id
        ]);
        $this->assertTrue($item1->validate());
        $this->assertNull($item1->circuit_voltage);

        // Test a bunch of valid
        foreach (['120V', '208V', '277V','480V','4.16kV','13kV'] as $voltage){
            $item1->circuit_voltage = $voltage;
            $this->assertTrue($item1->validate());
        }

        // Test a non-valid
        $item1->circuit_voltage = '100V';
        $this->assertFalse($item1->validate());

        // Test the mutator's ability to convert numeric voltages to names
        foreach (['120', '240', '277', 480, 4160, '13000'] as $voltage){
            $item1->circuit_voltage = $voltage;
            $this->assertTrue($item1->validate(),"invalid voltage: $voltage");
        }

    }

    function test_it_validates_method_of_proving(){
        $item1 =  factory(\Jlab\Epas\Model\PlantItem::class)->create([
            'plant_id' => 'pi1',
            'plant_parent_id' => $this->globalParent->plant_id
        ]);
        $this->assertTrue($item1->validate());
        $this->assertNull($item1->method_of_proving);

        // Test a bunch of valid
        foreach (['ZEV', 'ZVV', 'VVU'] as $method){
            $item1->method_of_proving = $method;
            $this->assertTrue($item1->validate());
        }

        // Test a non-valid
        $item1->method_of_proving = 'BBU';
        $this->assertFalse($item1->validate());

    }

}
