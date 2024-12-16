<?php

namespace Jlab\Epas\Tests\HCO;

use Jlab\Epas\Model\Component;

class ComponentTest extends HcoTestCase {

    protected Component $testComponent;

    public function setUp(): void {
        parent::setUp();

        $this->testComponent = Component::where('name', 'MJC4A18')->first();
        if (!$this->testComponent) {
            throw new \Exception("Unable to set test component.  Is HCO data loaded into srm_owner schema?");
        }
    }

    public function test_it_finds_plant_group() {
        $this->assertEquals('Accelerator', $this->testComponent->plantGroup());
    }

    public function test_it_makes_plant_item() {
        $item = $this->testComponent->toPlantItem();
        $this->assertEquals('Accelerator', $item->plant_group);
        $this->assertEquals(1, $item->is_plant_item);
        $this->assertEquals("HCO_COMPONENT_ID-11812", $item->plant_id);
        $this->assertEquals("999_ARC4", $item->plant_parent_id);
        $this->assertEquals("MJC4A18", $item->description);
        $this->assertEquals("ARC4", $item->location);
        $this->assertEquals("Dipoles", $item->plant_type);
        $this->assertEquals( "HCO", $item->data_source);
    }

    public function test_it_finds_existing() {
        $this->assertTrue($this->testComponent->existsInDatabase());
    }

}
