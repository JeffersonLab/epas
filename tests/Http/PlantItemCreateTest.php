<?php


namespace Jlab\Epas\Tests\Http;
use Inertia\Testing\Assert;
use Jlab\Epas\Tests\TestCase;

class PlantItemCreateTest extends HttpTestCase
{

    /** @test */
    function it_returns_page_data()
    {
        $response = $this->actingAs($this->adminUser)
            ->get(route('plant_items.create'));
        $response->assertStatus(200);

        $response->assertInertia(function (Assert $page) {
            $page->has('plantItem')
                ->has('formFieldOptions')
                ->has('formFieldOptions.plantGroupOptions')
                ->has('formFieldOptions.methodOfProvingOptions')
                ->has('formFieldOptions.circuitVoltageOptions');
        });
        $response->assertViewIs('jlab-epas::app');
    }


}
