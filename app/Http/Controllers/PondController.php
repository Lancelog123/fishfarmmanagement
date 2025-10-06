<?php

namespace App\Http\Controllers;

use App\Models\Pond;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PondController extends Controller
{
    /**
     * List all ponds.
     */
    public function index()
    {
        $ponds = Pond::all()->map(function($pond) {
            return [
                'id'            => $pond->id,
                'name'          => $pond->name,
                'type'      => $pond->type,    
                'category'  => $pond->category,  
                'location'      => $pond->location,
                'created_at'    => $pond->created_at,
                'updated_at'    => $pond->updated_at,
            ];
        });

        return response()->json(['data' => $ponds]);
    }

    /**
     * Show a specific pond.
     */
    public function show($id)
    {
        $pond = Pond::find($id);

        if (!$pond) {
            return response()->json(['message' => 'Pond not found'], 404);
        }

        return response()->json([
            'pond' => [
                'id'            => $pond->id,
                'name'          => $pond->name,
              
                'type'      => $pond->type,    
                'category'  => $pond->category,  
                'location'      => $pond->location,
                'created_at'    => $pond->created_at,
                'updated_at'    => $pond->updated_at,
            ]
        ]);
    }

    /**
     * Create a new pond.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'type'     => 'required|in:grow_out,fingerlings,breeders',
            'category' => 'required|in:mud,concrete',
            'location' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pond = Pond::create($request->all());

        return response()->json([
            'message' => 'Pond created successfully',
            'pond'    => $pond
        ], 201);
    }

    /**
     * Update pond details.
     */
    public function update(Request $request, $id)
    {
        $pond = Pond::find($id);

        if (!$pond) {
            return response()->json(['message' => 'Pond not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
    'type'     => 'required|in:grow_out,fingerlings,breeders',
    'category' => 'required|in:mud,concrete',
    'location' => 'nullable|string|max:255',

        ]);
        

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pond->update($request->all());

        return response()->json([
            'message' => 'Pond updated successfully',
            'pond'    => $pond
        ]);
    }

    /**
     * Delete a pond.
     */
    public function destroy($id)
    {
        $pond = Pond::find($id);

        if (!$pond) {
            return response()->json(['message' => 'Pond not found'], 404);
        }

        $pond->delete();

        return response()->json(['message' => 'Pond deleted successfully']);
    }
}
