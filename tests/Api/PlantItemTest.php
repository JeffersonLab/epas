<?php


namespace Jlab\Epas\Tests\Api;


use Jlab\Epas\Model\User;
use Jlab\Epas\Model\PlantItem;
use Illuminate\Support\Facades\Config;

class PlantItemTest extends ApiTestCase
{
    protected $plantItem;
    protected $plantItemAdminUser;

    function setup(): void{
        parent::setUp();
        $this->plantItem = factory(PlantItem::class)->create([
            'plant_id' => 'pid1',       // will get uppercased automatically!
            'plant_parent_id' => 'pid0',// will get uppercased automatically!
            'description' => 'Test Item',
        ]);
        $this->plantItemAdminUser = factory(User::class)->create([
            'username' => 'zaphod',
            'firstname' => 'Zaphod',
            'lastname' => 'Beeblebrox',
            'is_admin' => false,
        ]);
        Config::set('epas.admins',[$this->plantItemAdminUser->username]);
    }

    function test_it_retrieves_plant_item_details(){
        $response = $this->call('GET',
            route('api.plant_items.item',[$this->plantItem->id])
        );
        $response->assertStatus(200);
        // The api uses camel-case for attributes!
        $this->assertEquals($this->plantItem->plant_id, $response->json()['plantId']);
        $this->assertEquals($this->plantItem->description, $response->json()['description']);
    }


    function test_it_authorizes_update(){
        // Ensure unauthorized user cannot edit
        $response = $this->put(
            route('api.plant_items.update',[$this->plantItem->id]),
            ['description' => 'New Description']
        );
        $response->assertStatus(403);               // not authorized

        // Global admin may
        $response = $this->actingAs($this->adminUser)
            ->put(route('api.plant_items.update',[$this->plantItem->id]),
                ['description' => 'New Description']
            );
        $response->assertStatus(200);               // authorized

        // Plant Item Admin may
        $response = $this->actingAs($this->plantItemAdminUser)
            ->put(route('api.plant_items.update',[$this->plantItem->id]),
                ['description' => 'New Description']
            );
        $response->assertStatus(200);               // authorized

    }

    function test_it_updates_plant_item_details(){
        $response = $this->actingAs($this->plantItemAdminUser)
          ->put(route('api.plant_items.update',[$this->plantItem->id]),
            ['description' => 'New Description']
        );
        $response->assertStatus(200);
        $this->assertEquals('New Description', $response->json()['description']);
    }


    function test_it_blocks_update_of_external_datasource_item(){
        $this->plantItem->data_source = 'HCO';
        $this->assertTrue($this->plantItem->save());
        // Global admin may not
        $response = $this->actingAs($this->adminUser)
            ->put(route('api.plant_items.update',[$this->plantItem->id]),
                ['description' => 'New Description']
            );
        $response->assertStatus(403);               // authorized

        // Plant Item Admin may not
        $response = $this->actingAs($this->plantItemAdminUser)
            ->put(route('api.plant_items.update',[$this->plantItem->id]),
                ['description' => 'New Description']
            );
        $response->assertStatus(403);               // authorized

    }

    function test_it_authorizes_create(){
        $formData = [
          'plant_id' => 'A Plant Id',
          'plant_parent_id' => $this->plantItem->plant_id,
          'description' => 'A Description',
          'plant_group' => 'Accelerator',
        ];
        // Ensure unauthorized user cannot edit
        $response = $this->post(
            route('api.plant_items.store'),$formData
        );
        $response->assertStatus(403);               // not authorized

        // Global admin may
        $response = $this->actingAs($this->adminUser)->post(
                route('api.plant_items.store'),$formData
            );
        $response->assertStatus(201);               // authorized, 201 == 'created'

        // Plant Item Admin is authorized to attempt, but should get an
        // error about duplicate insertion when trying.
        $response = $this->actingAs($this->plantItemAdminUser)->post(
                route('api.plant_items.store'),$formData
            );
        $response->assertStatus(422);

        // When testing with non-Oracle database such as sqlite we'll only get
        // a generic Database Error rather than the more specific message
        $this->assertTrue(in_array($response->json()['message'],['Plant Id already exists','Database Error']));
    }

    public function test_it_retrieves_isolation_points(){
        $isolationPoints = factory(PlantItem::class, 3)->create([
            'plant_parent_id' => $this->plantItem->plant_id,
            'is_isolation_point' => true,
        ]);
        // Ensure we created them successfully
        $this->assertCount(3, PlantItem::where('is_isolation_point',true)->get());

        // Now via API
        $response = $this->get(route('api.plant_items.data.isolation_points'));
        $response->assertStatus(200);
        $this->assertCount(3, $response->json());

        // Now with an extra search filter
        // TODO figure out how to test search without actual search available
        //$response = $this->get(route('api.plant_items.data.isolation_points',['search' => $isolationPoints->first()->plant_id]));
        //$this->assertCount(1, $response->json());
    }

    function test_it_authorizes_delete(){

        $plantItem1 = factory(PlantItem::class)->create([
            'plant_id' => 'parent',       // will get uppercased automatically!
            'plant_parent_id' => $this->plantItem->plant_id,// will get uppercased automatically!
            'description' => 'Parent Test Item',
        ]);

        $plantItem2 = factory(PlantItem::class)->create([
            'plant_id' => 'child',       // will get uppercased automatically!
            'plant_parent_id' => 'parent',// will get uppercased automatically!
            'description' => 'Child Test Item',
        ]);
        // Ensure unauthorized user cannot delete
        $response = $this->delete(
            route('api.plant_items.delete',[$plantItem2->id])
        );
        $response->assertStatus(403);               // not authorized

        // Plant Item Admin may not delete parent item
        $response = $this->actingAs($this->plantItemAdminUser)->delete(
            route('api.plant_items.delete',[$plantItem1->id])
        );
        $response->assertStatus(403);

        // But Plant Item Admin may delete child item
        $response = $this->actingAs($this->plantItemAdminUser)->delete(
            route('api.plant_items.delete',[$plantItem2->id])
        );
        $response->assertStatus(200);
    }

}
