<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\NewsFeedController;
use App\Admin\Controllers\AdminController;
use App\Admin\Controllers\ActionController;
use App\Admin\Controllers\UserManagementController;
use App\Admin\Controllers\ContentModerationController;
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

Route::group([
    'prefix' => 'admins',
], function () {
    Route::post('/create', [AdminController::class, 'create'])->name('admin.create');
    Route::post('/create-super', [AdminController::class, 'createSuperAdmin'])->name('admin.createSuper');
    Route::post('/login', [AdminController::class, 'login'])->name('admin.login');

    Route::group([
        'middleware' => ['auth:admin']
    ], function () {
        Route::get('/', [AdminController::class, 'index'])->name('admin.index');
        Route::get('/permissions', [AdminController::class, 'getPermissions'])->name('admin.permissions');
        Route::post('/logout', [AdminController::class, 'logout'])->name('admin.logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/civilians/dashboard', [UserManagementController::class, 'getCivilianCounts'])->name('admin.civilianCounts');
        Route::get('/representatives/dashboard', [UserManagementController::class, 'getRepresentativeCounts'])->name('admin.representativeCounts');
        Route::get('/petitions/dashboard', [ContentModerationController::class, 'petitionStats'])->name('admin.petitionStats');
        Route::get('/contents', [ContentModerationController::class, 'getContents'])->name('admin.contents');
        Route::get('/civilians', [UserManagementController::class, 'getCivilians'])->name('admin.civilians');
        Route::get('/representatives', [UserManagementController::class, 'getRepresentatives'])->name('admin.representatives');
        Route::post('/states', [ActionController::class, 'addState'])->name('admin.addState');
        Route::post('/local-governments', [ActionController::class, 'addLocalGovernment'])->name('admin.addLocalGovernment');
        Route::post('/constituencies', [ActionController::class, 'addConstituency'])->name('admin.addConstituency');
        Route::post('/districts', [ActionController::class, 'addDistrict'])->name('admin.addDistrict');
        Route::post('/positions', [ActionController::class, 'addPosition'])->name('admin.addPosition');
        Route::post('/parties', [ActionController::class, 'addParty'])->name('admin.addParty');
        Route::put('/states/{id}', [ActionController::class, 'updateState'])->name('admin.updateState');
        Route::put('/local-governments/{id}', [ActionController::class, 'updateLocalGovernment'])->name('admin.updateLocalGovernment');
        Route::put('/constituencies/{id}', [ActionController::class, 'updateConstituency'])->name('admin.updateConstituency');
        Route::put('/districts/{id}', [ActionController::class, 'updateDistrict'])->name('admin.updateDistrict');
        Route::put('/positions/{id}', [ActionController::class, 'updatePosition'])->name('admin.updatePosition');
        Route::put('/parties/{id}', [ActionController::class, 'updateParty'])->name('admin.updateParty');
        Route::delete('/states/{id}', [ActionController::class, 'deleteState'])->name('admin.deleteState');
        Route::delete('/local-governments/{id}', [ActionController::class, 'deleteLocalGovernment'])->name('admin.deleteLocalGovernment');
        Route::delete('/constituencies/{id}', [ActionController::class, 'deleteConstituency'])->name('admin.deleteConstituency');
        Route::delete('/districts/{id}', [ActionController::class, 'deleteDistrict'])->name('admin.deleteDistrict');
        Route::delete('/positions/{id}', [ActionController::class, 'deletePosition'])->name('admin.deletePosition');
        Route::delete('/parties/{id}', [ActionController::class, 'deleteParty'])->name('admin.deleteParty');
        Route::get('/contents/{id}', [ContentModerationController::class, 'getContent'])->name('admin.content');
        Route::delete('/reports/{id}', [ContentModerationController::class, 'deleteReport'])->name('admin.deleteReport');
        Route::delete('/petitions/{id}', [ContentModerationController::class, 'deletePetition'])->name('admin.deletePetition');
        Route::post('/petitions/ignore/{id}', [ContentModerationController::class, 'ignorePetition'])->name('admin.ignorePetition');
        Route::post('/reports/ignore/{id}', [ContentModerationController::class, 'ignoreReport'])->name('admin.ignoreReport');
        Route::post('/accounts/approve/{accountId}', [UserManagementController::class, 'approveAccount'])->name('admin.approve');
        Route::post('/accounts/decline/{accountId}', [UserManagementController::class, 'declineAccount'])->name('admin.decline');
        Route::post('/accounts/suspend/{accountId}', [UserManagementController::class, 'suspendAccount'])->name('admin.suspend');
        Route::post('/accounts/reinstate/{accountId}', [UserManagementController::class, 'reinstateAccount'])->name('admin.reinstate');
        Route::delete('/accounts/delete/{accountId}', [UserManagementController::class, 'deleteAccount'])->name('admin.delete');
        Route::get('/accounts/{accountId}', [UserManagementController::class, 'showAccount'])->name('admin.account');
    });
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
    Route::post('refresh', [AuthController::class, 'refresh'])->name('refresh');

    Route::group([
        'middleware' => ['auth:api', 'activated']
    ], function () {
        Route::post('login', [AuthController::class, 'login'])->name('login')->withoutMiddleware('auth:api');
        Route::post('onboard', [AuthController::class, 'onboard'])->name('onboard');
        Route::get('onboard', [AuthController::class, 'indexOnboard'])->name('indexOnboard');
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
    Route::get('/status', [AccountController::class, 'status'])->name('status');
    Route::get('/notifications', [AccountController::class, 'notifications'])->name('notifications');
    Route::post('/profile/upload/{type}', [AccountController::class, 'upload'])->name('account.upload');
    Route::post('/profile/update', [AccountController::class, 'update'])->name('account.update');
    Route::get('/{id}', [AccountController::class, 'show'])->name('account.show');
});

Route::group([
    'prefix' => 'posts',
], function () {

    Route::get('/{id}', [PostController::class, 'show'])->name('post.show');
    Route::group([
        'middleware' =>  ['auth:api', 'activated']
    ], function () {

        Route::get('/', [HomePageController::class, 'postsIndex'])->name('postsIndex');
        Route::post('/', [PostController::class, 'create'])->name('post.create');
        Route::get('/petitions/{id}/signatures', [PostController::class, 'getSignees'])->name('getSignees');
        Route::get('/eye-witness-reports/{id}/approvals', [PostController::class, 'getApprovals'])->name('getApprovals');
        Route::post('/petitions/{id}/sign', [PostController::class, 'signPetition'])->name('signPetition');
        Route::post('/eye-witness-reports/{id}/approve', [PostController::class, 'approveReport'])->name('approveReport');
        Route::post('{id}/like', [PostController::class, 'like'])->name('post.like');
        Route::post('{id}/repost', [PostController::class, 'repost'])->name('post.repost');
        Route::post('{id}/bookmark', [PostController::class, 'bookmark'])->name('post.bookmark');
        Route::get('/{id}/share', [PostController::class, 'share'])->name('post.share');

        Route::post('/{postId}/comment/{commentId?}', [CommentController::class, 'create'])->name('comment.create');
        Route::get('/{id}/comments', [CommentController::class, 'comments'])->name('comments');
        Route::delete('/{id}', [PostController::class, 'delete'])->name('post.delete');
    });

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

Route::group([
    'prefix' => 'feeds',
    'middleware' => ['auth:api', 'activated']
], function () {
    Route::get('/', [NewsFeedController::class, 'index'])->name('feeds.index');
});

Route::get('/', function () {
    return response()->json([
        'message' => 'Pong!',
        'status' => 'success'
    ]);
});


Route::get('/permissions', function () {
    $permissions = DB::table('permissions')
        ->select('id', 'name')
        ->orderBy('id', 'asc')
        ->get();

    return response()->json($permissions, 200);
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
