<?php

namespace Jlab\Epas\Tests\Http;

use Jlab\Epas\Tests\TestCase;

class MiddlewareTest extends HttpTestCase
{

    /** @test */
    function it_returns_default_root_view()
    {
        $response = $this->get(route('plant_items.index'));
        $response->assertStatus(200);
        $response->assertViewIs('jlab-epas::app');
    }


}
