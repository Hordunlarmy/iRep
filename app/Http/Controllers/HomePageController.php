<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\HomePageResource;

class HomePageController extends Controller
{
    public function index(Request $request)
    {
        try {
            $criteria = $request->only([
                'search', 'state', 'position', 'local_government', 'sort_by',
                'sort_order', 'page', 'page_size']);

            $result = $this->homeFactory->getRepresentatives($criteria);

            return response()->json([
            'data' => HomePageResource::collection($result['data']),
            'meta' => [
                'total' => (int) $result['total'],
                'current_page' => (int) $result['current_page'],
                'last_page' => (int) $result['last_page'],
                'page_size' => (int) $criteria['page_size'] ?? 10,
            ],
        ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch representatives ' . $e->getMessage()], 500);
        }
    }

}
