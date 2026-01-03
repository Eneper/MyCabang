<?php

use App\Console\Commands\SubscribeMqtt;

it('returns null when name is missing', function () {
    $cmd = new SubscribeMqtt();
    expect($cmd->validateAndFormatPayload(['id' => '123']))->toBeNull();
});

it('returns null when id is missing', function () {
    $cmd = new SubscribeMqtt();
    expect($cmd->validateAndFormatPayload(['name' => 'John']))->toBeNull();
});

it('formats message correctly for valid payload', function () {
    $cmd = new SubscribeMqtt();
    expect($cmd->validateAndFormatPayload(['name' => 'John', 'id' => '123']))
        ->toBe('Received data - name: John, id: 123');
});
