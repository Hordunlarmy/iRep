<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PingController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountSearchController;
use Illuminate\Support\Facades\Cache;

Route::group([
    'middleware' => 'api',
    'prefix' => 'auth'
], function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth:api');
    Route::post('register', [AuthController::class, 'register'])->withoutMiddleware('auth:api');
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::get('me', [AuthController::class, 'me']);
    Route::get('redirect/{provider}', [AuthController::class, 'redirect'])->withoutMiddleware('auth:api');
    Route::get('callback/{provider}', [AuthController::class, 'callback'])->withoutMiddleware('auth:api');
    Route::post('activate', [AuthController::class, 'activateAccount'])->withoutMiddleware('auth:api');


});

Route::get('/', [PingController::class, 'ping']);
Route::get('/redis-test', function () {
    Cache::put('test', 'Redis is working!', 10);
    return Cache::get('test');
});
Route::get('/accounts/search', [AccountSearchController::class, 'search']);