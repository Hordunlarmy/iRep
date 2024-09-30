<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Database\Factories\AccountFactory;
use Illuminate\Support\Facades\DB;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    protected $db;
    protected $accountFactory;

    /**
     * Create a new Controller instance and initialize the database connection.
     *
     * @return void
     */
    public function __construct()
    {
        $this->db = DB::connection()->getPdo();
        $this->accountFactory = new AccountFactory();
    }

    /**
     * Generate a token response.
     *
     * @param  string  $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function tokenResponse($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
