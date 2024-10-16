<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OnboardAccountRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use App\Models\Account;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'account_type' => 'required|integer|exists:account_types,id',
                'email' => 'required|email|unique:accounts,email|max:255',
                'password' => 'required|string|min:8',
            ]);

            $account = $this->accountFactory->createAccount($validated);

            return response()->json([
                'account_id' => $account->id,
                'account_type' => $account->account_type
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['error' => 'Validation failed.', 'details' => $errors], 422);

        } catch (\Exception $e) {
            Log::error('Account creation failed.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Account creation failed.'], 500);
        }
    }

    public function activateAccount(Request $request)
    {
        $email = $request->input('email');
        $otp = $request->input('otp');

        $activated = $this->accountFactory->activateAccount($email, $otp);

        if (!$activated) {
            return response()->json(['error' => 'Invalid OTP'], 400);
        }

        $token = Auth::login($activated);
        return $this->tokenResponse($token);

    }

    public function resendActivation(Request $request)
    {
        $email = $request->input('email');

        $this->accountFactory->resendActivation($email);

        return response()->json(['message' => 'Activation email sent.'], 200);
    }

    public function onboard(OnboardAccountRequest $request)
    {
        $user = Auth::user();

        $validated = $request->validated();
        $validated['account_type'] = $user->account_type;
        $validated['id'] = $user->id;

        $account = $this->accountFactory->insertAccountDetails($validated);

        return response()->json(['account_id' => $account->id], 201);

    }

    public function login(Request $request)
    {
        $request->validate([
        'email' => 'required|email|exists:accounts,email',
        'password' => 'required'
        ]);

        $credentials = $request->only('email', 'password');

        $result = $this->accountFactory->getAccount($credentials['email']);
        $resultArray = json_decode(json_encode($result), true);

        $user = new Account(null, $resultArray);

        if ($user) {
            if (Hash::check($credentials['password'], $user->password)) {
                Log::info('User authenticated');
                $token = Auth::login($user);

                return $this->tokenResponse($token);
            } else {
                return response()->json(['error' => 'Incorrect credentials'], 401);
            }
        }

        return response()->json(['error' => 'Incorrect credentials'], 401);
    }

    public function redirect($provider)
    {
        // For OAuth 2.0 providers like Google, Facebook, etc., use stateless
        return Socialite::driver($provider)->stateless()->redirect();
    }

    public function callback($provider)
    {
        $socialUser = Socialite::driver($provider)->stateless()->user();
        Log::info('Social User Info:', ['socialUser' => json_encode($socialUser)]);

        $user = $this->accountFactory->getAccount($socialUser->getEmail());

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

        $token = Auth::login($user);

        return $this->tokenResponse($token);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        Auth::invalidate(true, true);

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->tokenResponse(Auth::refresh(true, true));

    }

}
