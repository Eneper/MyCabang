<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

class TellerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_teller_dashboard_requires_teller_role()
    {
        $user = User::factory()->create(['role' => 'nasabah']);
        $response = $this->actingAs($user)->get('/teller/dashboard');
        $response->assertStatus(403);

        $teller = User::factory()->create(['role' => 'teller']);
        $response2 = $this->actingAs($teller)->get('/teller/dashboard');
        $response2->assertStatus(200);
        $response2->assertSeeText('Teller Dashboard');
    }

    public function test_queue_endpoint_returns_customers_and_sets_current()
    {
        $teller = User::factory()->create(['role' => 'teller']);
        Customer::factory()->count(3)->create();

        // Ensure cache empty
        Cache::forget('teller_current_customer');

        $response = $this->actingAs($teller)->getJson('/teller/api/queue');
        $response->assertStatus(200)->assertJsonStructure(['customers','current']);

        $data = $response->json();
        $this->assertNotNull($data['current']);
        $this->assertCount(3, $data['customers']);
    }

    public function test_serve_sets_current()
    {
        $teller = User::factory()->create(['role' => 'teller']);
        $customers = Customer::factory()->count(2)->create();

        $id = $customers->first()->id;
        $response = $this->actingAs($teller)->postJson('/teller/api/serve', ['customer_id' => $id], ['X-CSRF-TOKEN' => csrf_token()]);
        $response->assertStatus(200)->assertJson(['current' => $id]);

        $this->assertEquals($id, Cache::get('teller_current_customer'));
    }

    public function test_serveNext_advances_queue()
    {
        $teller = User::factory()->create(['role' => 'teller']);
        $customers = Customer::factory()->count(3)->create();

        // set current to first
        Cache::put('teller_current_customer', $customers->first()->id);

        $response = $this->actingAs($teller)->postJson('/teller/api/serve/next', [], ['X-CSRF-TOKEN' => csrf_token()]);
        $response->assertStatus(200)->assertJsonStructure(['current']);
        $next = $response->json('current');
        $this->assertEquals($customers->get(1)->id, $next);
        $this->assertEquals($next, Cache::get('teller_current_customer'));
    }
}
