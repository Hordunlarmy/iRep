<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\AccountResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{
    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function profile()
    {
        $currentUser = Auth::user();

        return response()->json([
        'id' => $currentUser->id,
        'name' => $currentUser->name,
        'email' => $currentUser->email,
        'account_type' => $currentUser->account_type,
    ]);
    }

    public function getAccount($id)
    {
        if (!is_int($id) && !ctype_digit($id)) {
            return response()->json([
                'message' => 'Invalid ID. The ID must be an integer.'
            ], 400);
        }

        $account = $this->accountFactory->getAccount((int) $id);

        if (!$account) {
            return response()->json([
                'message' => 'Account not found.'
            ], 404);
        }

        return response()->json(new AccountResource($account));
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
