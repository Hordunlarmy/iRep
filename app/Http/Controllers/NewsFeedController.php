<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsFeedController extends Controller
{
    public function index(Request $request)
    {
        try {
            $criteria = $request->only([
                'search', 'sort_by', 'sort_order', 'page', 'page_size',
                'place_geocode_name', 'source_name', 'entity_value',
            ]);
            $result = $this->newsFeedFactory->getNewsFeed($criteria);

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch news feed ' . $e->getMessage()], 500);
        }
    }
}
