<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\FaceDetection;

class FaceDetectionService
{
    /**
     * Store a face detection from payload array
     * Expected keys:
     * - customer_id | cust_id | id
     * - recommendations (array, optional)
     * - timestamp (optional)
     * - status (optional)
     */
    public function storeFromPayload(array $payload): FaceDetection
    {
        // 1. Ambil customer code (prioritas ke customer_id)
        $custCode = $payload['customer_id']
            ?? ($payload['cust_id'] ?? ($payload['id'] ?? null));

        // 2. Ambil recommendations (array of objects)
        $recommendations = $payload['recommendations'] ?? [];

        // 3. Metadata dasar (simpan payload AI)
        $metadata = [
            'timestamp' => $payload['timestamp'] ?? now()->toISOString(),
            'status' => $payload['status'] ?? 'unknown',
        ];

        // Simpan raw recommendations ke metadata
        if (!empty($recommendations)) {
            $metadata['recommendations'] = $recommendations;
        }

        // 4. Cari customer
        $customer = null;
        if ($custCode) {
            $customer = Customer::where('cust_code', $custCode)->first();
            if ($customer) {
                $metadata['matched_by'] = 'customer_id';
            }
        }

        if (!$customer) {
            $metadata['matched_by'] = 'none';
        }

        // 5. (OPSIONAL) Simpan rekomendasi ringkas ke customer table
        if ($customer && !empty($recommendations)) {
            $customer->rekomendasi = collect($recommendations)
                ->sortBy('rank')
                ->map(fn ($r) => $r['product_name'] ?? 'Unknown')
                ->implode(', ');
            $customer->save();
        }

        // 6. Simpan FaceDetection event
        return FaceDetection::create([
            'name' => $customer?->name,   // ambil dari customer
            'customer_id' => $customer?->id,
            'metadata' => $metadata,      // JSON full AI payload (aman & fleksibel)
        ]);
    }
}
