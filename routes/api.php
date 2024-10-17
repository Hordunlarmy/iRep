<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return response()->json([
        'message' => 'Pong!',
        'status' => 'success'
    ]);
});

Route::get('/account-types', function () {
    $accountTypes = DB::table('account_types')
        ->select('id', 'name')
        ->orderBy('id', 'asc')
        ->get();

    return response()->json($accountTypes, 200);
});

Route::get('/representatives', [AccountController::class, 'listRepresentatives'])
    ->name('listRepresentatives')
    ->middleware('auth:api', 'activated');

Route::group([
    'prefix' => 'auth'
], function () {
    Route::post('register', [AuthController::class, 'register'])->name('register');
    Route::get('redirect/{provider}', [AuthController::class, 'redirect'])->name('redirect');
    Route::get('callback/{provider}', [AuthController::class, 'callback'])->name('callback');
    Route::post('activate', [AuthController::class, 'activateAccount'])->name('activate');
    Route::post('resend', [AuthController::class, 'resendActivation'])->name('resend');

    Route::group([
        'middleware' => ['auth:api', 'activated']
    ], function () {
        Route::post('login', [AuthController::class, 'login'])->name('login')->withoutMiddleware('auth:api');
        Route::post('onboard', [AuthController::class, 'onboard'])->name('onboard');
        Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    });
});

Route::group([
    'prefix' => 'accounts',
    'middleware' => ['auth:api', 'activated']
], function () {
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profile/upload/{type}', [AccountController::class, 'upload'])->name('upload');
    Route::post('/profile/update', [AccountController::class, 'update'])->name('update');
    Route::get('/{id}', [AccountController::class, 'show'])->name('show');
});




Route::group([
    'prefix' => 'posts'
], function () {
    Route::get('/', [PostController::class, 'index'])->name('index');
    Route::post('/', [PostController::class, 'create'])->name('create');
    Route::get('/{id}', [PostController::class, 'show'])->name('show');
    Route::post('/petitions/{id}/sign', [PostController::class, 'signPetition'])->name('signPetition');
    Route::post('/eye-witness-reports/{id}/approve', [PostController::class, 'approveReport'])->name('approveReport');
    Route::post('{id}/like', [PostController::class, 'like'])->name('like');
    Route::post('{id}/repost', [PostController::class, 'repost'])->name('repost');
    Route::post('{id}/bookmark', [PostController::class, 'bookmark'])->name('bookmark');
    Route::get('/{id}/share', [PostController::class, 'share'])->name('share');

    Route::post('/{id}/comment', [CommentController::class, 'create'])->name('create');
    Route::get('/{id}/comments', [CommentController::class, 'comments'])->name('comments');

});

Route::group([
    'prefix' => 'chats',
    'middleware' => ['auth:api']], function () {
        Route::post('/send', [ChatController::class, 'send'])->name('send');
        Route::get('/{id}', [ChatController::class, 'index'])->name('index');
    });
