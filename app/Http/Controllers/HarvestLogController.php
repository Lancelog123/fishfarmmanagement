<?php

namespace App\Http\Controllers;

use App\Models\HarvestLog;
use App\Models\Pond;
use App\Models\Net;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HarvestLogController extends Controller
{
    // Display all harvest logs (optionally filtered by pond)
    public function index(Request $request)
    {
        $query = HarvestLog::with('pond');

        if ($request->has('pond_id')) {
            $query->where('pond_id', $request->pond_id);
        }

        $harvests = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'data' => $harvests
        ]);
    }

    // Store new harvest log
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pond_id'        => 'required|exists:ponds,id',
            'net_id'         => 'nullable|exists:nets,id', // added for per-net harvest
            'type'           => 'required|in:grow_out,fingerlings',
            'species'        => 'required|string',
            'size_inch'      => 'required|numeric|min:0.1',
            'fish_qty'       => 'required|integer|min:1',
            'price_per_unit' => 'required|numeric|min:0',
            'buyer_name'     => 'nullable|string'
        ]);
    
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
    
        $harvestQty = $request->fish_qty;
        $total = $harvestQty * $request->price_per_unit;
    
        $pond = Pond::find($request->pond_id);
        if (!$pond) {
            return response()->json(['message' => 'Pond not found'], 404);
        }
    
        if ($request->type === 'grow_out') {
            // Reset all nets
            Net::where('pond_id', $pond->id)->update(['quantity' => 0]);
        } else {
            // Fingerlings per net
            if (!$request->net_id) {
                return response()->json(['message'=>'net_id is required for fingerlings harvest'], 422);
            }
    
            $net = Net::find($request->net_id);
            if (!$net) {
                return response()->json(['message'=>'Net not found'], 404);
            }
    
            $deduct = min($net->quantity, $harvestQty);
            $net->quantity -= $deduct;
            $net->save();
        }
    
        $harvest = HarvestLog::create([
            'pond_id'        => $request->pond_id,
            'type'           => $request->type,
            'species'        => $request->species,
            'size_inch'      => $request->size_inch,
            'fish_qty'       => $request->fish_qty,
            'price_per_unit' => $request->price_per_unit,
            'total_amount'   => $total,
            'buyer_name'     => $request->buyer_name,
            'net_id'         => $request->net_id ?? null, // optional
        ]);
    
        return response()->json([
            'message' => 'Harvest log recorded successfully',
            'harvest' => $harvest
        ], 201);
    }
    

    // Show a single harvest log
    public function show($id)
    {
        $harvest = HarvestLog::with('pond')->find($id);
        if (!$harvest) {
            return response()->json(['message' => 'Harvest not found'], 404);
        }

        return response()->json($harvest);
    }

    // Delete a harvest log
    public function destroy($id)
    {
        $harvest = HarvestLog::find($id);
        if (!$harvest) {
            return response()->json(['message' => 'Harvest not found'], 404);
        }

        $harvest->delete();

        return response()->json(['message' => 'Harvest log deleted successfully']);
    }
}
