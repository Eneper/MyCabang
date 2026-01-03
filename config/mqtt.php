<?php

return [
    'host' => env('MQTT_HOST', 'broker.hivemq.com'),
    'port' => env('MQTT_PORT', 1883),
    'topic' => env('MQTT_TOPIC', 'bank/cabang01/priority_alert'),
    'webhook_secret' => env('MQTT_WEBHOOK_SECRET', null),
];
