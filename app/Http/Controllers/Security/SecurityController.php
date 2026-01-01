<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FaceDetection;
use App\Models\Customer;
use App\Services\FaceDetectionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SecurityController extends Controller
{
    // main dashboard
    public function index()
    {
        // initial page render; detections are polled by JS
        return view('dashboard.securitydashboard');
    }

    // list recent face detections
    public function faceIndex()
    {
        $detections = FaceDetection::orderBy('created_at', 'desc')->limit(50)->get();
        return response()->json(['detections' => $detections]);
    }

    // show single detection detail
    public function show($id)
    {
        $d = FaceDetection::findOrFail($id);
        return response()->json(['detection' => $d]);
    }

    // confirm detection and optionally create queue entry
    public function confirm(Request $request, $faceDetectionId)
    {
        $d = FaceDetection::findOrFail($faceDetectionId);

        // optionally link to existing customer by id or create a queue entry
        $customerId = $request->input('customer_id');
        if ($customerId) {
            $customer = Customer::find($customerId);
            if ($customer) {
                $d->customer_id = $customer->id;
            }
        } else {
            // create a simple customer record using name from detection
            $name = $d->name ?? 'Tamu ' . Str::upper(Str::random(4));
            $customer = Customer::create(['name' => $name]);
            $d->customer_id = $customer->id;
        }

        $d->confirmed_at = now();
        $d->save();

        // enqueue customer by adding to a simple cache-based queue (ids array)
        $queue = Cache::get('security_queue', []);
        $queue[] = $d->customer_id;
        Cache::put('security_queue', $queue);

        return response()->json(['success' => true, 'detection' => $d, 'queue' => $queue]);
    }

    // webhook for mqtt messages (optional, for mqtt->http bridge)
    public function mqttWebhook(Request $request, FaceDetectionService $svc)
    {
        // optional secret header
        $secret = env('MQTT_WEBHOOK_SECRET');
        if ($secret) {
            $header = $request->header('X-MQTT-SECRET');
            if (!$header || $header !== $secret) {
                return response()->json(['error' => 'unauthorized'], 401);
            }
        }

        $data = $request->only(['name','photo','cust_id','metadata']);

        $d = $svc->storeFromPayload($data);

        return response()->json(['success' => true, 'detection' => $d]);
    }
}
