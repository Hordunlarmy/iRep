<?php

namespace App\Admin\Controllers;

use Illuminate\Http\Request;
use App\Admin\Factories\AdminFactory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    protected $adminFactory;

    public function __construct(AdminFactory $adminFactory)
    {
        $this->adminFactory = $adminFactory;
    }

    /**
     * Create a new admin.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|unique:admins,username',
            'password' => 'required|string|min:8',
            'permissions' => 'array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $admin = $this->adminFactory->createAdmin($validatedData);

        return response()->json([
            'message' => 'Admin created successfully.',
            'admin_id' => $admin,
        ], 201);
    }

    public function createSuperAdmin(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|unique:admins,username',
            'password' => 'required|string|min:8',
        ]);

        $admin = $this->adminFactory->createSuperAdmin($validatedData);

        return response()->json([
            'message' => 'Super admin created successfully.',
            'admin_id' => $admin,
        ], 201);
    }

    public function login(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = $this->adminFactory->getAdmin($validatedData['username']);

        if (!$admin || !Hash::check($validatedData['password'], $admin->password)) {
            return response()->json(['error' => 'Invalid credentials.'], 401);
        }

        $token = Auth::login($admin);

        return response()->json(array_merge(
            $this->tokenResponse($token)->original,
            ['account_type' => $admin->account_type]
        ));
    }

}
