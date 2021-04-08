<?php

namespace Jlab\Epas\Tests\Api;

use Jlab\Epas\Model\User;
use Illuminate\Support\Facades\Hash;
use Jlab\Epas\Tests\TestCase;

class ApiTestCase extends TestCase
{
    protected $adminUser;

    function setup(): void{
        parent::setUp();

        $this->adminUser = factory(User::class)->create([
            'username' => 'jdoe',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'is_admin' => true,
            'password' => Hash::make('testing123')
        ]);
    }


}
