<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AssignmentController extends Controller
{
    /**
     * List all assignments.
     */
    public function index()
    {
        $assignments = Assignment::with(['user', 'pond', 'net'])->get();
        return response()->json($assignments);
    }

    /**
     * Get assignments for a specific worker (by user_id).
     * Always returns an array (empty if none).
     */
    public function getByWorker($user_id)
    {
        $assignments = Assignment::with(['pond', 'net'])
            ->where('user_id', $user_id)
            ->get();

        return response()->json($assignments);
    }

    /**
     * Create a new assignment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'pond_id' => 'nullable|exists:ponds,id',
            'net_id'  => 'nullable|exists:nets,id',
            'task'    => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $assignment = Assignment::create($request->all());

        return response()->json([
            'message' => 'Assignment created successfully',
            'assignment' => $assignment
        ], 201);
    }

    /**
     * Show a specific assignment.
     */
    public function show($id)
    {
        $assignment = Assignment::with(['user', 'pond', 'net'])->find($id);

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        return response()->json($assignment);
    }

    /**
     * Update an assignment.
     */
    public function update(Request $request, $id)
    {
        $assignment = Assignment::find($id);

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $assignment->update($request->all());

        return response()->json([
            'message' => 'Assignment updated successfully',
            'assignment' => $assignment
        ]);
    }

    /**
     * Delete an assignment.
     */
    public function destroy($id)
    {
        $assignment = Assignment::find($id);

        if (!$assignment) {
            return response()->json(['message' => 'Assignment not found'], 404);
        }

        $assignment->delete();

        return response()->json(['message' => 'Assignment deleted successfully']);
    }
}
