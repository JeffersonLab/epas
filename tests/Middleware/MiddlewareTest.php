<?php

namespace Jlab\Epas\Tests\Middleware;

use Jlab\Epas\Tests\TestCase;

class MiddlewareTest extends TestCase
{

    /** @test */
    function it_returns_default_root_view()
    {
        $this->setPublicPath();

        $response = $this->get(route('plant_items.index'));
        $response->assertStatus(200);
        $response->assertViewIs('jlab-epas::app');
    }

    /**
     *  Ensure that tests can find public assets such as mix-manifest.json
     */
    protected function setPublicPath()
    {
        $this->app->bind('path.public', function () {
            return realpath(__DIR__ . '/../../public');
        });
    }
}
