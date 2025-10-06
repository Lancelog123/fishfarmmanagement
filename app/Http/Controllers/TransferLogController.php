<?php

namespace App\Http\Controllers;

use App\Models\TransferLog;
use App\Models\Net;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransferLogController extends Controller
{
    public function index()
    {
        return TransferLog::with(['user', 'fromNet', 'toNet', 'fromPond'])->get();
    }

    public function store(Request $request)
    {
        // Validate input
        $validator = Validator::make($request->all(), [
            'user_id'      => 'required|exists:users,id',
            'from_pond_id' => 'required|exists:ponds,id', // breeder pond
         
            'to_net_id'    => 'required|exists:nets,id',
            'quantity'     => 'required|integer|min:1',
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $toNet = Net::find($request->to_net_id);
        if (!$toNet) {
            return response()->json(['message' => 'Target net not found'], 400);
        }
    
        // ✅ Do NOT deduct from breeder pond
        // Only increase the target net
        $toNet->quantity += $request->quantity;
        $toNet->save();
    
        // Create log including from_pond_id
        $transfer = TransferLog::create([
            'user_id'      => $request->user_id,
            'from_net_id'  => null,                   // breeder pond has no net
            'from_pond_id' => $request->from_pond_id, // store breeder pond
            'to_net_id'    => $toNet->id,
            'quantity'     => $request->quantity,
        ]);
    
        return response()->json([
            'message'  => 'Transfer recorded successfully',
            'transfer' => $transfer
        ], 201);
    }
    

    public function show($id)
    {
        $transfer = TransferLog::with(['user', 'fromNet', 'toNet', 'fromPond'])->find($id);
        if (!$transfer) return response()->json(['message' => 'Not found'], 404);
        return response()->json($transfer);
    }

    public function destroy($id)
    {
        $transfer = TransferLog::find($id);
        if (!$transfer) return response()->json(['message' => 'Not found'], 404);

        // Revert stock only if fromNet exists (skip for breeder ponds)
        if ($transfer->from_net_id) {
            $transfer->fromNet->quantity += $transfer->quantity;
            $transfer->fromNet->save();
        }

        $transfer->toNet->quantity -= $transfer->quantity;
        $transfer->toNet->save();

        $transfer->delete();
        return response()->json(['message' => 'Transfer deleted']);
    }
}
