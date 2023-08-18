<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);

Route::group(['middleware'=>['token.auth', 'cors']], function() {
    Route::get('/me', [UserController::class, 'me']);
    Route::post('/add-money', [UserController::class, 'addMoney']);
    Route::post('/buy-cookie', [UserController::class, 'buyCookie']);
});