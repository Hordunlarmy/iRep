<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Database\Factories\SearchFactory;

class AccountSearchController extends Controller
{
    protected $searchFactory;

    public function __construct(SearchFactory $searchFactory)
    {
        $this->searchFactory = $searchFactory;
    }

    /**
     * Search for accounts based on criteria passed in the request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

     public function search (Request $request)
      {
             // Extract search criteria from the request
             $criteria = $request->only(['name', 'email', 'phone_number', 'account_type', 'state', 'local_government']);

             // Pass criteria to the factory's search method
             $accounts = $this->searchFactory->searchAccounts($criteria);
     
             // Return the result as JSON
             return response()->json([
                 'success' => true,
                 'data' => $accounts
             ]);
     }
}
