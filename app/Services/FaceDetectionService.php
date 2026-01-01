<?php

namespace App\Services;

use App\Models\FaceDetection;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class FaceDetectionService
{
    /**
     * Store a face detection from payload array
     * Expected keys: name, photo (base64 or url), customer_id, metadata
     */
    public function storeFromPayload(array $payload): FaceDetection
    {
        $name = $payload['name'] ?? null;
        $photo = $payload['photo'] ?? null;
        $customerId = $payload['cust_id'] ?? ($payload['customer_id'] ?? null);
        $metadata = $payload['metadata'] ?? null;

        $photoPath = null;

        if ($photo) {
            // base64 data URI
            if (is_string($photo) && str_starts_with($photo, 'data:image')) {
                // data:image/png;base64,....
                if (preg_match('/^data:(image\/[^;]+);base64,(.+)$/', $photo, $m)) {
                    $mime = $m[1];
                    $b64 = $m[2];
                    $ext = explode('/', $mime)[1] ?? 'jpg';
                    $data = base64_decode($b64);
                    $filename = 'faces/' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $ext;
                    Storage::disk('public')->put($filename, $data);
                    $photoPath = $filename;
                }
            } elseif (filter_var($photo, FILTER_VALIDATE_URL)) {
                // download URL
                try {
                    $resp = Http::timeout(10)->get($photo);
                    if ($resp->ok()) {
                        $contentType = $resp->header('Content-Type');
                        $ext = 'jpg';
                        if ($contentType && preg_match('#image/([a-z0-9]+)#', $contentType, $mm)) {
                            $ext = $mm[1];
                        }
                        $filename = 'faces/' . now()->format('Ymd_His') . '_' . Str::random(8) . '.' . $ext;
                        Storage::disk('public')->put($filename, $resp->body());
                        $photoPath = $filename;
                    }
                } catch (\Exception $e) {
                    // ignore download failures
                }
            }
        }

        $d = FaceDetection::create([
            'name' => $name,
            'photo' => $photoPath,
            'metadata' => $metadata,
            'customer_id' => $customerId,
        ]);

        return $d;
    }
}
