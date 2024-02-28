<?php


namespace Jlab\Epas\Tests\Http;

use Inertia\Testing\AssertableInertia as Assert;

class PlantItemPageTest extends HttpTestCase
{

    /** @test */
    function it_returns_page_data()
    {
        $this->withoutMix();  //@see https://github.com/orchestral/testbench/issues/241
        $response = $this->get(route('plant_items.index'));
        $response->assertStatus(200);

        // @see https://github.com/claudiodekker/inertia-laravel-testing/tree/2.0.0
        $response->assertInertia(function (Assert $page) {
            $page->has('plantItems')
                ->has('formFieldOptions')
                ->has('formFieldOptions.plantGroupOptions')
                ->has('formFieldOptions.methodOfProvingOptions')
                ->has('formFieldOptions.circuitVoltageOptions');
        });
        $response->assertViewIs('jlab-epas::app');
    }
}
