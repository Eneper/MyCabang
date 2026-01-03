<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Queue;
use Illuminate\Http\Request;
use App\Http\Controllers\UserQueController;

class UserQueueTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_queue_view_requires_nasabah_role()
    {
        $user = User::factory()->create(['role' => 'teller']);
        $res = $this->actingAs($user)->get('/customer/queue');
        $res->assertStatus(403);

        $nasabah = User::factory()->create(['role' => 'nasabah']);
        $res2 = $this->actingAs($nasabah)->get('/customer/queue');
        $res2->assertStatus(200);
        $res2->assertSeeText('Antrian Anda');
    }

    public function test_userqueue_controller_index_returns_active_queues_json()
    {
        $u = User::factory()->create(['role' => 'nasabah']);
        Queue::factory()->count(2)->create(['user_id' => $u->id, 'status' => 'active']);
        Queue::factory()->create(['user_id' => $u->id, 'status' => 'served']);

        // simulate an AJAX/json request
        $request = Request::create('/','GET',[],[],[],['HTTP_ACCEPT' => 'application/json']);

        $this->be($u);
        $controller = new UserQueController();
        $resp = $controller->index($request);

        $this->assertIsObject($resp);
        $content = $resp->getContent();
        $this->assertStringContainsString('"data"', $content);
    }

    public function test_show_returns_404_for_other_users()
    {
        $u1 = User::factory()->create(['role' => 'nasabah']);
        $u2 = User::factory()->create(['role' => 'nasabah']);
        $q = Queue::factory()->create(['user_id' => $u1->id, 'status' => 'active']);

        $this->be($u2);
        $request = Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']);
        $controller = new UserQueController();
        $resp = $controller->show($request, $q->id);

        // Should be a 404 Json response
        $this->assertEquals(404, $resp->getStatusCode());
    }
}
