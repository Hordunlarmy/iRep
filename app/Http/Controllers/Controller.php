<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Database\Factories\AccountFactory;
use Database\Factories\PostFactory;
use Database\Factories\CommentFactory;
use Database\Factories\MessageFactory;
use Database\Factories\HomePageFactory;
use Database\Factories\NewsFeedFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendNotification;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected $db;
    protected $accountFactory;
    protected $postFactory;
    protected $commentFactory;
    protected $messageFactory;
    protected $homeFactory;
    protected $newsFeedFactory;

    /**
     * Create a new Controller instance and initialize the database connection.
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->accountFactory = new AccountFactory();
        $this->postFactory = new PostFactory();
        $this->commentFactory = new CommentFactory();
        $this->messageFactory = new MessageFactory();
        $this->homeFactory = new HomePageFactory();
        $this->newsFeedFactory = new NewsFeedFactory();
    }

    /**
     * Generate a token response.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tokenResponse($token, $statusCode = 200, $expires_in = null)
    {
        if (is_null($expires_in)) {
            $expires_in = Auth::factory()->getTTL() * 60;
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expires_in,
        ], $statusCode);
    }

    /**
     * Toggle an action (like, unlike, repost e.t.c) on a post or comment.
     *
     * @param  string  $entity
     * @param  string  $actionType
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleAction($entity, $actionType, $id)
    {
        $this->findEntity($entity, $id);

        $accountId = Auth::id();
        $result = $this->{$entity . 'Factory'}->toggleAction($actionType, $id, $accountId);

        if ($result === 'added') {
            $entityData = $this->findEntity($entity, $id);

            $notificationMessages = [
                'likes' => [
                    'title' => 'Someone liked your {entity}',
                    'body'  => Auth::user()->name . ' liked your {entity}',
                ],
                'reposts' => [
                    'title' => 'Someone reposted your {entity}',
                    'body'  => Auth::user()->name . ' reposted your {entity}',
                ],
                'bookmarks' => [
                    'title' => 'Someone bookmarked your {entity}',
                    'body'  => Auth::user()->name . ' bookmarked your {entity}',
                ],
            ];

            $entityType = $entity;
            $notificationTemplate = $notificationMessages[$actionType] ?? [
                'title' => 'Activity on your {entity}',
                'body'  => Auth::user()->name . ' interacted with your {entity}',
            ];

            $notification = [
                'account_id' => $entityData->author_id,
                'entity_id'  => $id,
                'title'      => str_replace('{entity}', $entityType, $notificationTemplate['title']),
                'body'       => str_replace('{entity}', $entityType, $notificationTemplate['body']),
            ];

            \Illuminate\Support\Facades\Log::info('Notification data: ' . json_encode($notification));
            SendNotification::dispatch($entity, $notification);
        }

        return response()->json(['message' => $result], 200);
    }

    /**
     * Find an entity (account, post, comment etc).
     *
     * @param  string  $type
     * @param  int  $id
     * @return mixed
     */
    public function findEntity($type, $id)
    {
        if ($type === 'post') {
            $data = $this->postFactory->getPost($id);
        } elseif ($type === 'comment') {
            $data = $this->commentFactory->getComment($id);
        } elseif ($type === 'account') {
            $data = $this->accountFactory->getAccount($id);
        } else {
            abort(400, 'Invalid entity type');
        }

        if (!$data) {
            abort(404, "{$type} not found");
        }

        return $data;
    }
}
