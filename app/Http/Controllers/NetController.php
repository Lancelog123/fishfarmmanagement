<?php

namespace App\Http\Controllers;

use App\Models\Net;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NetController extends Controller
{
    // Get nets for a pond
    public function index(Request $request)
    {
        $pond_id = $request->query('pond_id');
        $nets = Net::where('pond_id', $pond_id)->get();
        return response()->json(['data' => $nets]);
    }

    // Create a new net
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'pond_id'    => 'required|exists:ponds,id',
            'identifier' => 'required|string|max:100',
            'quantity'   => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $net = Net::create($request->all());
        return response()->json(['message' => 'Net created successfully', 'net' => $net], 201);
    }

    // Update an existing net
    public function update(Request $request, $id)
    {
        $net = Net::findOrFail($id);

        $request->validate([
            'identifier' => 'required|string|max:100',
            'quantity'   => 'nullable|integer|min:0',
        ]);

        $net->update([
            'identifier' => $request->identifier,
            'quantity'   => $request->quantity,
        ]);

        return response()->json([
            'message' => 'Net updated successfully',
            'net' => $net
        ]);
    }

    // Optional: Delete a net
    public function destroy($id)
    {
        $net = Net::findOrFail($id);
        $net->delete();

        return response()->json([
            'message' => 'Net deleted successfully'
        ]);
    }
}
