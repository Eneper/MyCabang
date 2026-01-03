<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\FaceDetection;
use App\Models\Customer;
use App\Services\FaceDetectionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use App\Models\Queue;
use App\Models\User;

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

    // get customer data by id
    public function getCustomer($customerId)
    {
        $customer = Customer::findOrFail($customerId);
        return response()->json(['customer' => $customer]);
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
            // Generate unique cust_code
            $cust_code = 'CUST' . str_pad(Customer::max('id') + 1, 5, '0', STR_PAD_LEFT);
            $customer = Customer::create(['name' => $name, 'cust_code' => $cust_code]);
            $d->customer_id = $customer->id;
        }

        $d->confirmed_at = now();
        $d->save();

        // enqueue customer by adding to a simple cache-based queue (ids array)
        $queue = Cache::get('security_queue', []);
        $queue[] = $d->customer_id;
        Cache::put('security_queue', $queue);

        // If the customer is linked to a user, or can be matched by email, create a persistent Queue record
        try {
            $cust = Customer::find($d->customer_id);
            if ($cust) {
                // attempt to link to a User by email if not already linked
                if (!$cust->user && !empty($cust->email)) {
                    $maybeUser = User::where('email', $cust->email)->first();
                    if ($maybeUser) {
                        $cust->user_id = $maybeUser->id;
                        $cust->save();
                    }
                }

                if ($cust->user) {
                    // create queue entry for the user so frontend user dashboard can find it
                    Queue::create([
                        'user_id' => $cust->user->id,
                        'number' => null,
                        'status' => 'active',
                        'note' => 'Enqueued via security confirmation',
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // swallow errors to avoid breaking the API; logging could be added here
        }

        // Notify user account if this customer is linked to a user
        if ($d->customer_id) {
            try {
                $customer = \App\Models\Customer::find($d->customer_id);
                if ($customer && $customer->user) {
                    $customer->user->notify(new \App\Notifications\QueueAssignedNotification($d->id, $customer->id));
                }
            } catch (\Throwable $e) {
                // swallow errors to avoid breaking the API; logging could be added here
            }
        }

        return response()->json(['success' => true, 'detection' => $d, 'queue' => $queue]);
    }

    // webhook for mqtt messages (optional, for mqtt->http bridge)
    public function mqttWebhook(Request $request, FaceDetectionService $svc)
    {
        // optional secret header
        // prefer configuration over direct env() so tests can override via config()
        $secret = config('mqtt.webhook_secret');
        if ($secret) {
            $header = $request->header('X-MQTT-SECRET');
            if (!$header || $header !== $secret) {
                return response()->json(['error' => 'unauthorized'], 401);
            }
        }

        $data = $request->only(['name', 'photo', 'cust_id', 'metadata']);

        $d = $svc->storeFromPayload($data);

        return response()->json(['success' => true, 'detection' => $d]);
    }
}
