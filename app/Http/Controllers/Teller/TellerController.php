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
        $finished = Cache::get('teller_finished', []);
        $customers = Customer::whereNotIn('id', $finished)->orderBy('id')->get();
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

    // API: mark a customer as finished (removed from active queue)
    public function finish(Request $request)
    {
        $id = $request->input('customer_id');
        if (!$id) {
            return response()->json(['error' => 'customer_id required'], 400);
        }

        $finished = Cache::get('teller_finished', []);
        if (!in_array($id, $finished)) {
            $finished[] = (int) $id;
            Cache::put('teller_finished', $finished);
        }

        // advance current to next available customer
        $remaining = Customer::whereNotIn('id', $finished)->orderBy('id')->pluck('id');
        $next = $remaining->first() ?? null;
        if ($next) {
            Cache::put('teller_current_customer', $next);
        } else {
            Cache::forget('teller_current_customer');
        }

        return response()->json(['finished' => $id, 'current' => $next]);
    }

    // API: provide recommendation payload for given customer id (demo)
    public function showRecommendation($customerId)
    {
        // In production this would call AI/model service.
        // Structure: title (produk), reason (alasan mengapa), explanation (penjelasan detail dari AI)
        $recommendations = [
            [
                'title' => 'Tabungan Prima',
                'reason' => 'Cocok untuk penabung rutin',
                'explanation' => 'Berdasarkan pola transaksi, nasabah melakukan setoran berkala setiap bulan. Tabungan Prima menawarkan bunga kompetitif dan kemudahan pencairan.'
            ],
            [
                'title' => 'Deposito Berjangka 3 bulan',
                'reason' => 'Promo bunga lebih tinggi',
                'explanation' => 'AI mendeteksi saldo idle yang cukup besar. Dengan mendepositokan dana untuk jangka pendek, nasabah dapat return lebih baik dibanding tabungan reguler.'
            ],
            [
                'title' => 'Kartu Kredit Silver',
                'reason' => 'Kelayakan berdasarkan scoring internal',
                'explanation' => 'Profil nasabah menunjukkan skor kredit yang baik dan aktivitas transaksi stabil. Kartu Silver memberikan benefit cashback dan asuransi perjalanan.'
            ],
        ];

        return response()->json(['customer_id' => (int) $customerId, 'products' => $recommendations]);
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

    // API: set a specific customer as currently served via URL param
    public function serveById($queueId)
    {
        Cache::put('teller_current_customer', $queueId);
        return response()->json(['current' => (int) $queueId]);
    }

    // API: return single customer info (JSON) for UI/detail
    public function showCustomer($customerId)
    {
        $customer = Customer::find($customerId);
        if (!$customer) {
            return response()->json(['error' => 'not_found'], 404);
        }

        $payload = [
            'id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'photo' => $customer->photo ? '/storage/' . $customer->photo : null,
            'profile' => $customer->profile ?? null,
        ];

        return response()->json($payload);
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
