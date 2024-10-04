<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAccountRequest;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function register(CreateAccountRequest $request)
    {
        try {
            $account = $this->accountFactory->createAccount($request->validated());
            Log::info('Account created successfully.', ['account_id' => $account->id]);

            return response()->json(['account_id' => $account->id], 201);
        } catch (\Exception $e) {
            Log::error('Account creation failed.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Account creation failed.'], 500);
        }
    }

    public function activateAccount(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');

        $verified = $this->accountFactory->activateAccount($email, $otp);

        if (!$verified) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        $token = auth()->login($verified);
        return $this->tokenResponse($token);

    }

    public function resendActivation(Request $request)
    {
        $email = $request->input('email');

        $this->accountFactory->resendActivation($email);

        return response()->json(['message' => 'Activation email sent.'], 200);
    }


    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        $user = Account::getAccount($this->db, $credentials['email']);

        if ($user) {
            if (Hash::check($credentials['password'], $user->password)) {
                Log::info('User authenticated');
                $token = auth()->login($user);

                return $this->tokenResponse($token);
            } else {
                return response()->json(['error' => 'Incorrect credentials'], 401);
            }
        }

        return response()->json(['error' => 'Incorrect credentials'], 401);
    }

    public function redirect($provider)
    {
        if ($provider == 'twitter') {
            $twitter = Socialite::driver($provider);
            $requestToken = $twitter->getRequestToken();
            Cache::put('oauth_request_token_' . request()->ip(), $requestToken, now()->addMinutes(10));
            return redirect()->away($twitter->getAuthorizationUrl($requestToken));
        }

        // For OAuth 2.0 providers like Google, Facebook, etc., use stateless
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback($provider)
    {
        if ($provider == 'twitter') {
            $socialUser = Socialite::driver($provider)->user();
        } else {
            $socialUser = Socialite::driver($provider)->stateless()->user();
        }
        Log::info('Social User Info:', ['socialUser' => json_encode($socialUser)]);

        $user = Account::getAccount($this->db, $socialUser->getEmail());

        if (!$user) {
            $userData = [
                'name' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'photo_url' => $socialUser->getAvatar(),
                'account_type' => 'social',
            ];
            $user = $this->accountFactory->createAccount($userData);
            $this->accountFactory->setEmailVerified($user->id);
        }

        $token = auth()->login($user);

        return $this->tokenResponse($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $currentUser = auth()->user();
        return response()->json([
        'id' => $currentUser->id,
        'name' => $currentUser->name,
        'email' => $currentUser->email,
        'account_type' => $currentUser->account_type,
    ]);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->tokenResponse(auth()->refresh());

    }

}
