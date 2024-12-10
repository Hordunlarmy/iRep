<?php

namespace App\Admin\Controllers;

use Illuminate\Http\Request;
use App\Admin\Factories\ContentModerationFactory;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Support\Facades\Log;

class ContentModerationController extends Controller
{
    protected $contentModerationFactory;

    public function __construct(ContentModerationFactory $contentModerationFactory)
    {
        parent::__construct();
        $this->contentModerationFactory = $contentModerationFactory;
    }

    public function petitionStats()
    {

        $post = $this->contentModerationFactory->getPostStats('petition');

        return response()->json($post);
    }

    public function getContents(Request $request)
    {
        try {
            $criteria = $request->only(['page', 'page_size', 'states', 'status',
            'reported', 'post_type']);
            $result = $this->contentModerationFactory->getContents($criteria);

            $posts = $result['data'];
            $meta = $result['meta'];

            return response()->json([
                'data' => PostResource::collection($posts),
                'meta' => $meta,
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch posts ' . $e->getMessage()], 500);
        }
    }

    public function deletePetition($id)
    {
        try {
            $result = $this->contentModerationFactory->deletePost($id, 'petition');

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete petition ' . $e->getMessage()], 500);
        }
    }

    public function deleteReport($id)
    {
        try {
            $result = $this->contentModerationFactory->deletePost($id, 'eyewitness');

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete report ' . $e->getMessage()], 500);
        }
    }

    public function ignorePetition($id)
    {
        try {
            $result = $this->contentModerationFactory->ignorePost($id, 'petition');

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to ignore petition ' . $e->getMessage()], 500);
        }
    }

    public function ignoreReport($id)
    {
        try {
            $result = $this->contentModerationFactory->ignorePost($id, 'eyewitness');

            return response()->json($result, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to ignore report ' . $e->getMessage()], 500);
        }
    }

}
