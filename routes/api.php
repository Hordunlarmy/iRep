<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomePageController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

Route::middleware('auth:api')->get('/auth/user', function (Request $request) {

    $user = Auth::user();

    $response = [
        'id' => $user->id,
        'name' => $user->name,
        'account_type' => $user->account_type,
        'email' => $user->email,
        'email_verified' => $user->email_verified,
    ];

    return response()->json($response, 200);
});

Route::get('/search', [HomePageController::class, 'search'])->name('search')
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
    Route::get('/representatives', [HomePageController::class, 'repIndex'])->name('repIndex');
    Route::post('/representatives/apply', [AccountController::class, 'applyForRep'])->name('applyForRep');
    Route::get('/profile', [AccountController::class, 'profile'])->name('profile');
    Route::post('/profile/upload/{type}', [AccountController::class, 'upload'])->name('account.upload');
    Route::post('/profile/update', [AccountController::class, 'update'])->name('account.update');
    Route::get('/{id}', [AccountController::class, 'show'])->name('account.show');
});

Route::group([
    'prefix' => 'posts',
    'middleware' => ['auth:api', 'activated']
], function () {
    Route::get('/', [HomePageController::class, 'postsIndex'])->name('postsIndex');
    Route::post('/', [PostController::class, 'create'])->name('post.create');
    Route::get('/{id}', [PostController::class, 'show'])->name('post.show');
    Route::post('/petitions/{id}/sign', [PostController::class, 'signPetition'])->name('signPetition');
    Route::post('/eye-witness-reports/{id}/approve', [PostController::class, 'approveReport'])->name('approveReport');
    Route::post('{id}/like', [PostController::class, 'like'])->name('post.like');
    Route::post('{id}/repost', [PostController::class, 'repost'])->name('post.repost');
    Route::post('{id}/bookmark', [PostController::class, 'bookmark'])->name('post.bookmark');
    Route::get('/{id}/share', [PostController::class, 'share'])->name('post.share');

    Route::post('/{postId}/comment/{commentId?}', [CommentController::class, 'create'])->name('comment.create');
    Route::get('/{id}/comments', [CommentController::class, 'comments'])->name('comments');

});

Route::group([
    'prefix' => 'comments',
    'middleware' => ['auth:api', 'activated']
], function () {
    Route::get('/', [CommentController::class, 'index'])->name('comment.index');
    Route::post('/', [CommentController::class, 'create'])->name('parentComment.create');
    Route::get('/{id}', [CommentController::class, 'show'])->name('comment.show');
    Route::post('/{id}/like', [CommentController::class, 'like'])->name('comment.like');
});

Route::group([
    'prefix' => 'chats',
    'middleware' => ['auth:api', 'activated']], function () {
        Route::post('/send', [ChatController::class, 'send'])->name('chat.send');
        Route::get('/unread', [ChatController::class, 'getUnreadMessages'])->name('chat.unread');
        Route::get('/{id}', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/{id}/read', [ChatController::class, 'markAsRead'])->name('chat.read');
        Route::delete('/messages/{id}', [ChatController::class, 'delete'])->name('chat.delete');
    });

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

Route::get('/states', function () {
    $states = DB::table('states')
        ->select('id', 'name')
        ->orderBy('id', 'asc')
        ->get();

    return response()->json($states, 200);
});

Route::get('/local-governments/{stateId}', function ($stateId) {
    $localGovernments = DB::table('local_governments')
        ->select('id', 'name')
        ->where('state_id', $stateId)
        ->orderBy('name', 'asc')
        ->get();

    return response()->json($localGovernments, 200);
});

Route::get('/positions', function () {
    $positions = DB::table('positions')
        ->select('id', 'title')
        ->orderBy('title', 'asc')
        ->get();

    return response()->json($positions, 200);
});

Route::get('/parties', function () {
    $parties = DB::table('parties')
        ->select('id', 'name', 'code')
        ->orderBy('name', 'asc')
        ->get();

    return response()->json($parties, 200);
});

Route::get('/constituencies/{stateId}', function ($stateId) {
    $constituencies = DB::table('constituencies')
        ->select('id', 'name')
        ->where('state_id', $stateId)
        ->orderBy('name', 'asc')
        ->get();

    return response()->json($constituencies, 200);
});

Route::get('/districts/{stateId}', function ($stateId) {
    $districts = DB::table('districts')
        ->select('id', 'name')
        ->where('state_id', $stateId)
        ->orderBy('name', 'asc')
        ->get();

    return response()->json($districts, 200);
});
