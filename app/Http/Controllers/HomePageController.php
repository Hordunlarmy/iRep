<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\HomePageResource;

class HomePageController extends Controller
{
    public function search(Request $request)
    {
        try {
            $criteria = $request->only([
                'search', 'sort_by', 'sort_order', 'page', 'page_size'
            ]);
            $result = $this->homeFactory->globalSearch($criteria);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to search ' . $e->getMessage()], 500);
        }
    }
    public function repIndex(Request $request)
    {
        try {
            $criteria = $request->only([
                'search', 'account_type', 'position', 'constituency', 'party',
                'district', 'state', 'local_government',
                'sort_by', 'sort_order', 'page', 'page_size']);

            $result = $this->homeFactory->getRepresentatives($criteria);

            return response()->json([
                'data' => HomePageResource::collection(
                    $result['data']
                )->map->toRepArray($request)->flatten(1),
                'meta' => [
                    'total' => (int) $result['total'],
                    'current_page' => (int) $result['current_page'],
                    'last_page' => (int) $result['last_page'],
                    'page_size' => (int) $criteria['page_size'] ?? 10,
                ],
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch representatives ' . $e->getMessage()
            ], 500);
        }
    }

    public function postsIndex(Request $request)
    {
        try {

            $criteria = $request->only([
                'search', 'sort_by', 'sort_order', 'page',
                'page_size', 'status', 'category', 'post_type'
            ]);
            $result = $this->homeFactory->getCommunityPosts($criteria);

            return response()->json([
            'data' => HomePageResource::collection($result['data'])->map->toPostArray()->flatten(1),
            'meta' => [
                'total' => (int) $result['total'],
                'current_page' => (int) $result['current_page'],
                'last_page' => (int) $result['last_page'],
                'page_size' => (int) $criteria['page_size'] ?? 10,
            ],
        ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch posts ' . $e->getMessage()], 500);
        }
    }
}
