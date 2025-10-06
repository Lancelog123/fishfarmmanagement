<?php

namespace App\Http\Controllers;

use App\Models\TransferRequest;
use App\Models\TransferLog;
use App\Models\Net;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransferRequestController extends Controller
{
    // 📨 CREATE TRANSFER REQUEST
    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_user_id' => 'required|integer',
            'to_user_id' => 'required|integer',
            'from_net_id' => 'required|integer',
            'to_net_id' => 'required|integer',
            'species' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
        ]);

        $requestObj = TransferRequest::create($validated);
        return response()->json(['message' => 'Transfer request sent successfully', 'request' => $requestObj]);
    }

    // 👀 FETCH TRANSFER REQUESTS
    public function index(Request $request)
    {
        $userId = $request->query('user_id');

        $query = TransferRequest::with(['fromUser', 'toUser'])
            ->orderByDesc('created_at');

        if ($userId) {
            $query->where('to_user_id', $userId)
                  ->orWhere('from_user_id', $userId);
        }

        return response()->json($query->get());
    }

    // ✅ APPROVE OR ❌ REJECT REQUEST
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $transfer = TransferRequest::findOrFail($id);
        $transfer->update(['status' => $validated['status']]);

        // If approved, update logs + nets
        if ($validated['status'] === 'approved') {
            DB::transaction(function () use ($transfer) {
                // Record in transfer_logs
                TransferLog::create([
                    'user_id' => $transfer->from_user_id,
                    'from_net_id' => $transfer->from_net_id,
                    'to_net_id' => $transfer->to_net_id,
                    'quantity' => $transfer->quantity,
                ]);

                // Deduct from sender
                Net::where('id', $transfer->from_net_id)->decrement('quantity', $transfer->quantity);

                // Add to receiver
                Net::where('id', $transfer->to_net_id)->increment('quantity', $transfer->quantity);
            });
        }

        return response()->json(['message' => 'Transfer request updated successfully']);
    }
}
