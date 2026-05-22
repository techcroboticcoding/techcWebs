<?php

use App\Models\Student;
use App\Models\Order;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Murid;

use App\Http\Controllers\ProfileController;


Route::post('/login', function (Request $request) {

    $user = User::where('email', $request->email)->first();

    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'message' => 'Email atau password salah'
        ], 401);
    }

    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'role'  => $user->role,
        'name'  => $user->name
    ]);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [ProfileController::class, 'getProfile']);
    Route::post('/profile/upload-photo', [ProfileController::class, 'uploadPhoto']);
    Route::get('/dashboard', [ProfileController::class, 'dashboard']);

    Route::get('/students', function () {
        return Murid::select('id', 'nama', 'logo')->get();
    });

});