<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PondController;
use App\Http\Controllers\NetController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\HarvestLogController;
use App\Http\Controllers\TransferLogController;
use App\Http\Controllers\StockingLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group.
|
*/

// 🔹 Authentication Routes
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

// 🔹 User Approval (Admin)
Route::get('/users/pending', [UserController::class, 'pending']);
Route::put('/users/{id}/approve', [UserController::class, 'approve']);
Route::put('/users/{id}/reject', [UserController::class, 'reject']);
Route::get('/users/workers/approved', [UserController::class, 'approvedWorkers']);

// 🔹 Pond Management
Route::apiResource('ponds', PondController::class);

// 🔹 Net Management
Route::apiResource('nets', NetController::class);

// 🔹 Assignments
Route::apiResource('assignments', AssignmentController::class);
Route::get('/assignments/worker/{user_id}', [AssignmentController::class, 'getByWorker']);

// 🔹 Harvest Logs
Route::get('harvest-logs', [HarvestLogController::class,'index']);
Route::post('harvest-logs', [HarvestLogController::class,'store']);
Route::get('harvest-logs/{id}', [HarvestLogController::class,'show']);
Route::delete('harvest-logs/{id}', [HarvestLogController::class,'destroy']);

// 🔹 Transfer Logs
Route::apiResource('transfer-logs', TransferLogController::class);

// 🔹 Stocking Logs
Route::get('stocking-logs', [StockingLogController::class,'index']);
Route::post('stocking-logs', [StockingLogController::class,'store']);
Route::delete('stocking-logs/{id}', [StockingLogController::class,'destroy']);

// ✅ Custom: Get stocking logs filtered by pond and worker
Route::get('stocking-logs/{pond_id}/worker/{user_id}', [StockingLogController::class,'getByWorker']);

// 🔹 Test Route
Route::get('/test', function () {
    return response()->json(['message' => 'API working!']);
});
