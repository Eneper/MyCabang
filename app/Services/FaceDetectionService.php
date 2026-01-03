<?php

namespace App\Services;

use App\Models\FaceDetection;

class FaceDetectionService
{
    /**
     * Store a face detection from payload array
     * Expected keys: name, id|cust_id|customer_id, metadata (optional)
     */
    public function storeFromPayload(array $payload): FaceDetection
    {
        $name = $payload['name'] ?? null;
        // accept either 'id', 'cust_id' or 'customer_id' for the customer id
        $customerId = $payload['id'] ?? $payload['cust_id'] ?? $payload['customer_id'] ?? null;
        $metadata = $payload['metadata'] ?? null;

        $d = FaceDetection::create([
            'name' => $name,
            'photo' => null,
            'metadata' => $metadata,
            'customer_id' => $customerId,
        ]);

        return $d;
    }
}
