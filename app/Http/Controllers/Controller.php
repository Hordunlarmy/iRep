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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
    }

    /**
     * Generate a token response.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tokenResponse($token, $expires_in = null)
    {
        if (is_null($expires_in)) {
            $expires_in = Auth::factory()->getTTL() * 60;
        }

        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $expires_in,
        ]);
    }

    public function toggleAction($entity, $actionType, $id)
    {
        $this->findEntity($entity, $id);

        $accountId = Auth::id();

        $result = $this->{$entity . 'Factory'}->toggleAction($actionType, $id, $accountId);

        if ($result) {
            return response()->json(['message' => $result], 200);
        }
        return response()->json(['message' => 'Action failed'], 400);
    }


    public function findEntity($type, $id)
    {
        if ($type === 'post') {
            $data = $this->postFactory->getPost($id);
        } elseif ($type === 'comment') {
            $data = $this->commentFactory->getComment($id);
        } elseif ($type === 'account') {
            $data = $this->accountFactory->getAccount($id);
        } else {
            return response()->json(['message' => 'Invalid entity type'], 400);
        }

        if (!$data) {
            return response()->json(['message' => "{$type} not found"], 404);
        }

        return $data;
    }

}
