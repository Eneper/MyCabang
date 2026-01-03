<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FaceDetectionService;

class SubscribeMqtt extends Command
{
    protected $signature = 'mqtt:subscribe {topic?}';
    protected $description = 'Subscribe to MQTT topic and store face detections (broker.hivemq.com:1883)';

    public function handle(FaceDetectionService $svc)
    {
        // Check for required library
        if (!class_exists('\PhpMqtt\Client\MqttClient')) {
            $this->error("php mqtt client not installed. Run: composer require php-mqtt/client");
            return 1;
        }

        $host = env('MQTT_HOST', 'broker.hivemq.com');
        $port = (int) env('MQTT_PORT', 1883);
        $topic = $this->argument('topic') ?: 'bank/cabang01/priority_data';
        $clientId = 'mycabang-' . uniqid();

        $this->info("Connecting to MQTT {$host}:{$port} topic={$topic}");

        $connectionSettings = new \PhpMqtt\Client\ConnectionSettings();
        $client = new \PhpMqtt\Client\MqttClient($host, $port, $clientId);

        // graceful shutdown flag
        $stop = false;

        // Attempt to register signal handlers when available (Unix)
        if (function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
            if (defined('SIGINT')) {
                pcntl_signal(SIGINT, function () use (&$stop, $client) {
                    $this->info('SIGINT received, disconnecting...');
                    $stop = true;
                    try { $client->interrupt(); } catch (\Throwable $e) { }
                });
            }
            if (defined('SIGTERM')) {
                pcntl_signal(SIGTERM, function () use (&$stop, $client) {
                    $this->info('SIGTERM received, disconnecting...');
                    $stop = true;
                    try { $client->interrupt(); } catch (\Throwable $e) { }
                });
            }
        }

        try {
            $client->connect($connectionSettings, true);

            $client->subscribe($topic, function ($topic, $message) use ($svc) {
                $this->info("Message received on {$topic}");

                $payload = @json_decode($message, true);
                if (!is_array($payload)) {
                    $this->warn('Invalid payload: must be JSON');
                    return;
                }

                $formatted = $this->validateAndFormatPayload($payload);
                if (! $formatted) {
                    $this->warn('Invalid payload: requires "name" and "id"');
                    return;
                }

                $this->info($formatted);

                // store to DB and notify dashboard
                $d = $this->processPayload($payload, $svc);
                if ($d) {
                    $this->info('Stored detection id: ' . $d->id);
                }
            }, 0);

            // Run loop until interrupted
            while (! $stop) {
                $client->loop(true);
                // small sleep to avoid tight loop if loop() returns
                usleep(100000);
            }

            // Disconnect cleanly
            try {
                $client->disconnect();
            } catch (\Throwable $e) {
                $this->warn('Error during disconnect: ' . $e->getMessage());
            }
        } catch (\Exception $e) {
            $this->error('MQTT error: ' . $e->getMessage());
            return 1;
        }

        $this->info('Subscriber stopped');
        return 0;
    }

    /**
     * Validate payload and return formatted string or null if invalid.
     *
     * @param array $payload
     * @return string|null
     */
   public function validateAndFormatPayload(array $payload): ?string
{
    // 1. Validasi field wajib
    if (empty($payload['customer_id']) || empty($payload['status'])) {
        return null;
    }

    $customerId = $payload['customer_id'];
    $timestamp  = $payload['timestamp'] ?? '-';
    $status     = $payload['status'];

    // 2. Ambil rekomendasi (array)
    $recommendations = $payload['recommendations'] ?? [];

    // 3. Format rekomendasi jadi string ringkas
    $formattedRecs = [];

    foreach ($recommendations as $rec) {
        $formattedRecs[] = sprintf(
            "%d. %s (%.2f)",
            $rec['rank'] ?? 0,
            $rec['product_name'] ?? 'Unknown',
            $rec['confidence'] ?? 0
        );
    }

    $recString = $formattedRecs
        ? implode(' | ', $formattedRecs)
        : 'No recommendation';

    // 4. Return string final
    return "AI Recommendation Received | customer_id: {$customerId} | time: {$timestamp} | status: {$status} | products: {$recString}";
}

    /**
     * Store payload via FaceDetectionService and broadcast to dashboard (security channel)
     *
     * @param array $payload
     * @param FaceDetectionService $svc
     * @return \App\Models\FaceDetection|null
     */
    public function processPayload(array $payload, FaceDetectionService $svc)
    {
        if (! $this->validateAndFormatPayload($payload)) {
            return null;
        }

        try {
            $d = $svc->storeFromPayload($payload);
            event(new \App\Events\FaceDetected($d));
            return $d;
        } catch (\Throwable $e) {
            // In tests prefer to bubble up the exception for visibility
            if (app()->runningUnitTests()) {
                throw $e;
            }

            \Illuminate\Support\Facades\Log::error('Failed to store/broadcast detection: ' . $e->getMessage());
            return null;
        }
    }
}
