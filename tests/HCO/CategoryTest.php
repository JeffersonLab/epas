<?php

namespace Jlab\Epas\Tests\HCO;

use Jlab\Epas\Model\Category;
use PHPUnit\Runner\Exception;

class CategoryTest extends HcoTestCase {

    protected Category $testCategory;

    public function setUp(): void
    {
        parent::setUp();

        $this->testCategory = Category::where('name','Trim Supply')->first();
        if (! $this->testCategory){
            throw new Exception("Unable to set test category.  Is HCO data loaded into srm_owner schema?");
        }
    }

    function test_it_finds_parent(){
        $this->assertEquals('Magnets', $this->testCategory->parentCategory->name);
    }

    function test_it_finds_facility_name() {
        $this->assertEquals('CEBAF', $this->testCategory->facilityName());
    }

    function test_it_finds_systems_recursively() {
        $category = $this->testCategory->parentCategory;
        // A direct child system
        $this->assertContains('LCW Valves', $category->systems()->pluck('name'));
        // An indirect child system via Box Power Supplies sub-category
        $this->assertContains('Dipoles', $category->systems()->pluck('name'));
    }

    function test_it_finds_direct_systems_only() {
        $category = $this->testCategory->parentCategory;
        // A direct child system
        $this->assertContains('LCW Valves', $category->systems(false)->pluck('name'));
        // An indirect child system via Box Power Supplies sub-category
        // which should not be present when we give argument to exclude subcategories
        $this->assertNotContains('Dipoles', $category->systems(false)->pluck('name'));
    }



}
