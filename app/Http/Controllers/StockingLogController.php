<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockingLog;
use App\Models\Net;

class StockingLogController extends Controller
{
    // Create new stocking log
    public function store(Request $request)
    {
        $request->validate([
            'user_id'     => 'required|exists:users,id',
            'pond_id'     => 'required|exists:ponds,id',
            'species'     => 'required|string',
            'quantity'    => 'required|integer|min:1',
            'action_type' => 'required|string',
            'action_date' => 'required|date',
            'hapa_name'   => 'nullable|string',
            'net_id'      => 'nullable|exists:nets,id',
        ]);

        $net_id = $request->net_id;

        // If a new hapa/net is specified
        if ($request->filled('hapa_name')) {
            $net = Net::create([
                'pond_id'    => $request->pond_id,
                'identifier' => $request->hapa_name,
                'quantity'   => 0,
            ]);
            $net_id = $net->id;
        }

        // Update net quantity
        if ($net_id) {
            $net = Net::find($net_id);
            $net->quantity += $request->quantity;
            $net->save();
        }

        // Create stocking log
        $log = StockingLog::create([
            'user_id'     => $request->user_id,
            'pond_id'     => $request->pond_id,
            'net_id'      => $net_id,
            'species'     => $request->species,
            'quantity'    => $request->quantity,
            'action_type' => $request->action_type,
            'action_date' => $request->action_date,
        ]);

        return response()->json([
            'message' => 'Stocking log created successfully',
            'log'     => $log
        ], 201);
    }

    // Get all stocking logs (only available stock, quantity > 0)
    public function index(Request $request)
    {
        $query = StockingLog::with('net')->where('quantity', '>', 0);

        if ($request->has('pond_id')) {
            $query->where('pond_id', $request->pond_id);
        }

        $stockings = $query->orderBy('action_date', 'desc')->get();

        return response()->json([
            'data' => $stockings
        ]);
    }

    // Get stocking logs by pond and worker (optional custom)
    public function getByWorker($pond_id, $user_id)
    {
        $logs = StockingLog::with('net')
            ->where('pond_id', $pond_id)
            ->where('user_id', $user_id)
            ->where('quantity', '>', 0)
            ->orderBy('action_date', 'desc')
            ->get();

        return response()->json([
            'data' => $logs
        ]);
    }

    // Delete a stocking log
    public function destroy($id)
    {
        $log = StockingLog::find($id);
        if (!$log) return response()->json(['message' => 'Stocking not found'], 404);

        // Optional: Adjust net quantity when deleting stocking
        if ($log->net_id) {
            $net = Net::find($log->net_id);
            if ($net) {
                $net->quantity -= $log->quantity;
                if ($net->quantity < 0) $net->quantity = 0;
                $net->save();
            }
        }

        $log->delete();

        return response()->json(['message' => 'Stocking deleted']);
    }
}
