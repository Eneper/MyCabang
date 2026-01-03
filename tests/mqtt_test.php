#!/usr/bin/env php
<?php
/**
 * MQTT Testing Script
 * Menguji MQTT integration dengan mengirim payload test ke API webhook
 */

// Configuration
$apiUrl = 'http://127.0.0.1:8000/security/api/mqtt/webhook';
$mqttSecret = null; // Set in .env if needed

// Test payloads
$testPayloads = [
    [
        'name' => 'Enver',
        'cust_id' => 'CUST00006',
        'metadata' => [
            'camera' => 'entrance_1',
            'timestamp' => date('Y-m-d H:i:s'),
            'confidence' => 0.95
        ]
    ],
    [
        'name' => 'Valdo',
        'cust_id' => 'CUST00007',
        'metadata' => [
            'camera' => 'entrance_2',
            'timestamp' => date('Y-m-d H:i:s'),
            'confidence' => 0.92
        ]
    ],
    [
        'name' => 'Unknown Customer',
        'cust_id' => 'CUST99999',
        'metadata' => [
            'camera' => 'lobby',
            'timestamp' => date('Y-m-d H:i:s'),
            'confidence' => 0.88
        ]
    ]
];

echo "====================================\n";
echo "MQTT Webhook Testing Script\n";
echo "====================================\n\n";

// Test each payload
foreach ($testPayloads as $index => $payload) {
    echo "Test " . ($index + 1) . ": Sending payload for " . $payload['name'] . "\n";
    echo "Payload: " . json_encode($payload, JSON_PRETTY_PRINT) . "\n\n";

    // Prepare request
    $ch = curl_init($apiUrl);
    
    $headers = [
        'Content-Type: application/json',
    ];
    
    if ($mqttSecret) {
        $headers[] = 'X-MQTT-SECRET: ' . $mqttSecret;
    }
    
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        echo "❌ Error: " . $error . "\n\n";
    } else {
        echo "HTTP Status: " . $httpCode . "\n";
        echo "Response: " . $response . "\n\n";
        
        if ($httpCode >= 200 && $httpCode < 300) {
            echo "✅ Success\n\n";
        } else {
            echo "❌ Failed\n\n";
        }
    }
    
    // Wait between requests
    sleep(1);
}

echo "====================================\n";
echo "Testing Complete!\n";
echo "====================================\n";
echo "\nNext steps:\n";
echo "1. Check Security Dashboard at http://127.0.0.1:8000/security/dashboard\n";
echo "2. Click on a detection to see full customer data\n";
echo "3. Click 'Konfirmasi & Buat Antrian' to enqueue the customer\n";
echo "4. Check Teller Dashboard to see the customer in queue\n";
?>
