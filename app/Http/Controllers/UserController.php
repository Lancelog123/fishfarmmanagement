<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Register a new user (default status = pending).
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'fullname' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email'    => 'required|email|unique:users,email',
            'phone'    => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role'     => 'in:admin,worker' // default worker if not given
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email'    => $request->email,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'worker',
            'status'   => 'pending',
        ]);

        return response()->json([
            'message' => 'User registered. Waiting for admin approval.',
            'user' => $user
        ], 201);
    }

    /**
     * Login (only approved users can log in).
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if ($user->status !== 'approved') {
            return response()->json(['message' => 'Account not approved'], 403);
        }

        return response()->json([
            'message' => 'Login successful',
            'user' => $user
        ]);
    }

    /**
     * List all pending users.
     */
    public function pending()
    {
        $users = User::where('status', 'pending')->get();
        return response()->json($users);
    }

    /**
     * Approve a user.
     */
    public function approve($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->status = 'approved';
        $user->save();

        return response()->json([
            'message' => 'User approved',
            'user' => $user
        ]);
    }

    /**
     * Reject a user.
     */
    public function reject($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $user->status = 'rejected';
        $user->save();

        return response()->json([
            'message' => 'User rejected',
            'user' => $user
        ]);
    }
    public function approvedWorkers()
{
    $workers = User::where('role', 'worker')
                   ->where('status', 'approved')
                   ->get();

    return response()->json($workers);
}
}
