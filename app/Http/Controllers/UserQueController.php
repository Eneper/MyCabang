<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Queue;

class UserQueController extends Controller
{
    /**
     * Show active queues for the authenticated user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $queues = Queue::where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('created_at')
            ->get();

        // if caller expects JSON, return data; otherwise return view
        if ($request->wantsJson()) {
            return response()->json(['data' => $queues]);
        }

        return view('user.userqueue', ['queues' => $queues]);
    }

    /**
     * Show details for a specific queue item.
     */
    public function show(Request $request, $queId)
    {
        $user = Auth::user();

        $queue = Queue::where('id', $queId)
            ->where('user_id', $user->id)
            ->first();

        if (!$queue) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Not found'], 404);
            }

            abort(404);
        }

        if ($request->wantsJson()) {
            return response()->json(['data' => $queue]);
        }

        // return a detail view if present, otherwise return the main queue view
        if (view()->exists('user.queue_detail')) {
            return view('user.queue_detail', ['queue' => $queue]);
        }

        return view('user.userqueue', ['queues' => collect([$queue])]);
    }

    /**
     * List notifications for the authenticated user.
     */
    public function notifications(Request $request)
    {
        $user = Auth::user();

        // uses the Notifiable trait on the User model
        $notes = $user->notifications()->orderBy('created_at', 'desc')->take(50)->get();

        if ($request->wantsJson()) {
            return response()->json(['data' => $notes]);
        }

        // return a small view if available, otherwise JSON
        if (view()->exists('user.notifications')) {
            return view('user.notifications', ['notifications' => $notes]);
        }

        return response()->json(['data' => $notes]);
    }
}