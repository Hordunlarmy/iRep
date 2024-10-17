<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\AccountResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UpdateProfileRequest;

class AccountController extends Controller
{
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile(Request $request)
    {
        $currentUser = Auth::user();

        $account = $this->findEntity('account', $currentUser->id);

        return response()->json((new AccountResource($account))->toProfileArray($request), 200);

    }

    public function upload(Request $request, $type)
    {
        try {
            $validated = $request->validate([
                'photo' => 'required|file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            $accountId = Auth::id();

            if ($type === 'profile') {
                $field = 'photo_url';
            } elseif ($type === 'cover') {
                $field = 'cover_photo_url';
            } else {
                return response()->json(['error' => 'Invalid photo type.'], 400);
            }

            $result = $this->accountFactory->uploadPhoto($field, $accountId, $validated['photo']);

            return response()->json([
                'photo_url' => $result
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->validator->errors()->all();
            return response()->json(['error' => 'Validation failed.', 'details' => $errors], 422);

        } catch (\Exception $e) {
            Log::error('Photo upload failed.', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Photo upload failed.'], 500);
        }
    }

    public function update(UpdateProfileRequest $request)
    {
        $accountId = Auth::id();
        $validated = $request->validated();

        $result = $this->accountFactory->updateAccount($accountId, $validated);

        if ($result) {
            return response()->json(['message' => 'Profile updated.'], 200);
        }

        return response()->json(['message' => 'Profile update failed.'], 400);
    }

    public function show($id, Request $request)
    {
        if (!is_int($id) && !ctype_digit($id)) {
            return response()->json([
                'message' => 'Invalid ID. The ID must be an integer.'
            ], 400);
        }

        $account = $this->findEntity('account', $id);

        return response()->json((new AccountResource($account))->toArray($request), 200);

    }

    public function listRepresentatives(Request $request)
    {
        {
            $criteria = $request->only([
                'search', 'state', 'position', 'local_government', 'sort_by',
                'sort_order']);

            $representatives = $this->accountFactory->getRepresentatives($criteria);

            return response()->json(AccountResource::collection($representatives));

        }
    }
}
