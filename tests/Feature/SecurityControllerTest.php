<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\FaceDetection;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

class SecurityControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_security_dashboard_requires_security_role()
    {
        $user = User::factory()->create(['role' => 'teller']);
        $response = $this->actingAs($user)->get('/security/dashboard');
        $response->assertStatus(403);

        $sec = User::factory()->create(['role' => 'security']);
        $response2 = $this->actingAs($sec)->get('/security/dashboard');
        $response2->assertStatus(200);
        $response2->assertSeeText('Security Dashboard');
    }

    public function test_faceIndex_returns_recent_detections()
    {
        $sec = User::factory()->create(['role' => 'security']);
        FaceDetection::factory()->count(3)->create();

        $res = $this->actingAs($sec)->getJson('/security/api/faces');
        $res->assertStatus(200)->assertJsonStructure(['detections']);
        $this->assertCount(3, $res->json('detections'));
    }

    public function test_show_returns_detection()
    {
        $sec = User::factory()->create(['role' => 'security']);
        $d = FaceDetection::create(['name' => 'Alice']);

        $res = $this->actingAs($sec)->getJson('/security/api/faces/' . $d->id);
        $res->assertStatus(200)->assertJsonPath('detection.id', $d->id);
    }

    public function test_confirm_creates_customer_and_enqueues()
    {
        $sec = User::factory()->create(['role' => 'security']);
        $d = FaceDetection::create(['name' => 'Bob']);

        $res = $this->actingAs($sec)->postJson('/security/api/faces/' . $d->id . '/confirm', [], ['X-CSRF-TOKEN' => csrf_token()]);
        $res->assertStatus(200)->assertJson(['success' => true]);

        $d->refresh();
        $this->assertNotNull($d->customer_id);
        $this->assertNotNull($d->confirmed_at);

        $queue = Cache::get('security_queue');
        $this->assertIsArray($queue);
        $this->assertContains($d->customer_id, $queue);
    }

    public function test_mqtt_webhook_stores_detection()
    {
        $payload = ['name' => 'WebhookUser', 'cust_id' => null, 'metadata' => ['camera' => 'c1']];

        $headers = [];
        if (config('mqtt.webhook_secret')) {
            $headers['X-MQTT-SECRET'] = config('mqtt.webhook_secret');
        }

        $res = $this->postJson('/security/api/mqtt/webhook', $payload, $headers);
        $res->assertStatus(200)->assertJson(['success' => true]);

        $this->assertDatabaseHas('face_detections', ['name' => 'WebhookUser']);
    }
}
