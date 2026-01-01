<?php

return [
    'host' => env('MQTT_HOST', '127.0.0.1'),
    'port' => env('MQTT_PORT', 1883),
    'topic' => env('MQTT_TOPIC', 'security/faces'),
    'webhook_secret' => env('MQTT_WEBHOOK_SECRET', null),
];