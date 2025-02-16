<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FileController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/helloworld', function (Request $request) {
    return response()->json([
        'message' => 'Hello, World!',
    ]);
});

Route::get('/test-load', function (Request $request) {
    $hostname = gethostname(); // Get container name
    Log::info("Handled by: $hostname"); // Log which container handled the request
    sleep(5); // Simulate slow request
    return response()->json(["message" => "Handled by $hostname"]);
});



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::resource('files', FileController::class)->middleware('auth:sanctum');
