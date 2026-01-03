<?php

use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Console\Commands\SubscribeMqtt;
use App\Services\FaceDetectionService;
use App\Events\FaceDetected;

uses(RefreshDatabase::class);

it('stores payload and dispatches FaceDetected event', function () {
    Event::fake();

    $cmd = new SubscribeMqtt();
    $svc = new FaceDetectionService();

    $payload = ['name' => 'Test User', 'id' => 'X123'];

    $d = $cmd->processPayload($payload, $svc);

    expect($d)->toBeInstanceOf(\App\Models\FaceDetection::class);

    // name saved, unknown customer ID should not be set to foreign key
    $this->assertDatabaseHas('face_detections', [
        'name' => 'Test User',
    ]);

    expect($d->customer_id)->toBeNull();
    expect($d->metadata['cust_id'])->toBe('X123');

    Event::assertDispatched(FaceDetected::class, function ($event) use ($d) {
        return $event->detection->id === $d->id;
    });
});
