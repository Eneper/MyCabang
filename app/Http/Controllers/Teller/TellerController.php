<?php

namespace App\Http\Controllers\Teller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use Illuminate\Support\Facades\Cache;

class TellerController extends Controller
{
    public function index()
    {
        // initial page render — data will be polled by JS
        return view('dashboard.tellerdashboard');
    }

    public function show(Customer $customer)
    {
        return view('dashboard.tellerprofile', compact('customer'));
    }

    // API: return full queue and current served id
    public function queue()
    {
        $customers = Customer::orderBy('id')->get();
        $current = Cache::get('teller_current_customer');
        // if no current, set to first in queue
        if (!$current && $customers->count()) {
            $current = $customers->first()->id;
            Cache::put('teller_current_customer', $current);
        }

        return response()->json([
            'customers' => $customers,
            'current' => $current,
        ]);
    }

    // API: set a specific customer as currently served
    public function serve(Request $request)
    {
        $id = $request->input('customer_id');
        if ($id) {
            Cache::put('teller_current_customer', $id);
        }
        return response()->json(['current' => $id]);
    }

    // API: advance to next customer in queue
    public function serveNext()
    {
        $customers = Customer::orderBy('id')->pluck('id');
        $current = Cache::get('teller_current_customer');

        if ($customers->isEmpty()) {
            Cache::forget('teller_current_customer');
            return response()->json(['current' => null]);
        }

        $index = $customers->search($current);
        if ($index === false) {
            $next = $customers->first();
        } else {
            $next = $customers->get($index + 1) ?? null;
        }

        if ($next) {
            Cache::put('teller_current_customer', $next);
        } else {
            // reached end — clear or keep last
            Cache::forget('teller_current_customer');
        }

        return response()->json(['current' => $next]);
    }
}
