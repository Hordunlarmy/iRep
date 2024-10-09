<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Database\Factories\AccountFactory;

class JWTAuthMiddleware
{
    protected $accountFactory;

    public function __construct(AccountFactory $accountFactory)
    {
        $this->accountFactory = $accountFactory;
    }

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();


        if (!$token) {
            return response()->json(['error' => 'Token not provided'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $payload = JWTAuth::setToken($token)->getPayload();
            $userId = $payload['sub'];

            $user = $this->accountFactory->getAccount($userId);

            if (!$user) {
                return response()->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            auth()->setUser($user);

            if (auth()->check()) {
                \Log::info('User is authenticated', ['user' => auth()->user()]);
            } else {
                \Log::warning('User not authenticated');
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'Token is invalid'], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
