<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\FaceDetectionService;

class SubscribeMqtt extends Command
{
    protected $signature = 'mqtt:subscribe {topic=security/faces}';
    protected $description = 'Subscribe to MQTT topic and store face detections (requires php-mqtt/client)';

    public function handle(FaceDetectionService $svc)
    {
        // lazy check for library
        if (!class_exists('\PhpMqtt\Client\MqttClient')) {
            $this->error("php mqtt client not installed. Run: composer require php-mqtt/client");
            return 1;
        }

        $host = env('MQTT_HOST', '127.0.0.1');
        $port = env('MQTT_PORT', 1883);
        $clientId = 'mycabang-' . uniqid();
        $topic = $this->argument('topic');

        $this->info("Connecting to MQTT {$host}:{$port} topic={$topic}");

        $connectionSettings = new \PhpMqtt\Client\ConnectionSettings();

        $client = new \PhpMqtt\Client\MqttClient($host, (int)$port, $clientId);

        try {
            $client->connect($connectionSettings, true);

            $client->subscribe($topic, function ($topic, $message) use ($svc) {
                $this->info("Message received on {$topic}");
                // expect JSON payload
                $payload = @json_decode($message, true);
                if (!is_array($payload)) {
                    $this->warn('Invalid payload: must be JSON');
                    return;
                }

                $svc->storeFromPayload($payload);
                $this->info('Stored detection');
            }, 0);

            // keep listening
            $client->loop(true);

            $client->disconnect();
        } catch (\Exception $e) {
            $this->error('MQTT error: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
