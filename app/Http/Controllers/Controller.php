<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Database\Factories\AccountFactory;
use Database\Factories\PostFactory;
use Database\Factories\CommentFactory;
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

    public function findPost($id)
    {
        $postData = $this->postFactory->getPost($id);

        if (!$postData) {
            return response()->json(['message' => 'post not found'], 404);
        }

        return $postData;
    }
}
