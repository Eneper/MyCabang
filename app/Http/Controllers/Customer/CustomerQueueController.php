<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerQueueController extends Controller
{
    // Display queue page for customer
    public function index()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        return view('user.userqueue', compact('customer'));
    }

    /**
     * Show notifications for the authenticated customer user.
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();

        $notes = $user->notifications()->orderBy('created_at', 'desc')->take(50)->get();

        if ($request->wantsJson()) {
            return response()->json(['data' => $notes]);
        }

        return view('user.notifications', ['notifications' => $notes]);
    }

    // API: Check if customer is in queue and their position
    public function queueStatus()
    {
        $user = Auth::user();
        $customer = Customer::where('user_id', $user->id)->first();

        if (!$customer) {
            return response()->json(['in_queue' => false, 'position' => null]);
        }

        // Check if in security queue (from security dashboard confirmation)
        $securityQueue = Cache::get('security_queue', []);
        $inSecurityQueue = in_array($customer->id, $securityQueue);

        // Check if in finished list
        $finished = Cache::get('teller_finished', []);
        $isFinished = in_array($customer->id, $finished);

        // Get current being served
        $currentId = Cache::get('teller_current_customer');
        $isCurrently = $currentId == $customer->id;

        // Get all customers not in finished list for position
        $allQueued = Customer::whereNotIn('id', $finished)
            ->orderBy('id')
            ->pluck('id')
            ->toArray();

        $position = array_search($customer->id, $allQueued) + 1; // +1 for human-readable position

        return response()->json([
            'in_queue' => $inSecurityQueue && !$isFinished,
            'currently_served' => $isCurrently,
            'position' => $position,
            'is_finished' => $isFinished,
            'total_in_queue' => count($allQueued),
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'cust_code' => $customer->cust_code,
            ]
        ]);
    }
}
