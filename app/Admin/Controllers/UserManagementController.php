<?php

namespace App\Admin\Controllers;

use Illuminate\Http\Request;
use App\Admin\Factories\UserManagementFactory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Http\Resources\AccountResource;

class UserManagementController extends Controller
{
    protected $userManagementFactory;

    public function __construct(UserManagementFactory $userManagementFactory)
    {
        parent::__construct();
        $this->userManagementFactory = $userManagementFactory;
    }

    public function getCivilianCounts()
    {
        $counts = $this->userManagementFactory->getAccountStats(1);

        return response()->json($counts);
    }

    public function getRepresentativeCounts()
    {
        $counts = $this->userManagementFactory->getAccountStats(2);

        return response()->json($counts);
    }

    public function getAccountsByType(Request $request, $accountType)
    {
        $params = $request->only(['search', 'status', 'page', 'page_size']);

        $accounts = $this->userManagementFactory->getAccounts($params, $accountType);

        return response()->json($accounts);
    }

    public function getCivilians(Request $request)
    {
        return $this->getAccountsByType($request, 1);
    }

    public function getRepresentatives(Request $request)
    {
        return $this->getAccountsByType($request, 2);
    }

    public function approveAccount($accountId)
    {
        $account = $this->userManagementFactory->approveAccount($accountId);

        return response()->json($account);
    }

    public function declineAccount($accountId)
    {
        $account = $this->userManagementFactory->disapproveAccount($accountId);

        return response()->json($account);
    }

    public function suspendAccount($accountId)
    {
        $account = $this->userManagementFactory->suspendAccount($accountId);

        return response()->json($account);
    }

    public function reinstateAccount($accountId)
    {
        $account = $this->userManagementFactory->unsuspendAccount($accountId);

        return response()->json($account);
    }

    public function deleteAccount($accountId)
    {
        $account = $this->userManagementFactory->deleteAccount($accountId);

        return response()->json($account);
    }

    public function showAccount($accountId, Request $request)
    {
        if (!is_int($accountId) && !ctype_digit($accountId)) {
            return response()->json([
                'message' => 'Invalid ID. The ID must be an integer.'
            ], 400);
        }

        $account = $this->findEntity('account', $accountId);

        return response()->json((new AccountResource($account))->toAdminViewArray($request), 200);

    }
}
