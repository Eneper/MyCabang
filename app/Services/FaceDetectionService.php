<?php

namespace App\Services;
use App\Models\Customer;
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
        $custCode = $payload['id'] ?? ($payload['cust_id'] ?? ($payload['customer_id'] ?? null));
        $recommendation = $payload['recommendation'] ?? null;
        $metadata = $payload['metadata'] ?? [];

        // normalize metadata to array
        if (! is_array($metadata)) {
            $metadata = ['raw_metadata' => $metadata];
        }

        $customer = null;
        if ($custCode) {
            $customer = Customer::where('cust_code', $custCode)->first();
            if ($customer) {
                $metadata['matched_by'] = 'cust_id';
            }
        }

        // 2. Fallback: match by name
        if (!$customer && $name) {
            $customer = Customer::where('name', $name)->first();
            if ($customer) {
                $metadata['matched_by'] = 'name';
            }
        }

        if (!$customer) {
            $metadata['matched_by'] = 'none';
        }

        return FaceDetection::create([
            'name' => $name,
            'customer_id' => $customer?->id,
            // 'photo' => $customer?->photo, // ⬅️ ambil foto dari customer
            'metadata' => $metadata,
        ]);

        // return $d;
    }
}
