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
    public function profile(Request $request)
    {
        $currentUser = Auth::user();

        $account = $this->findEntity('account', $currentUser->id);

        return response()->json((new AccountResource($account))->toProfileArray($request), 200);

    }

    public function getAccount($id, Request $request)
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
