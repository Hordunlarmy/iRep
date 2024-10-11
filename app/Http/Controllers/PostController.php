<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function create(CreatePostRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['creatorId'] = Auth::id();
            $validatedData['targetRepresentativeId'] = $validatedData['target_representative_id'] ?? null;

            if ($request->hasFile('media')) {
                $mediaFiles = $request->file('media');

                if (!is_array($mediaFiles)) {
                    $mediaFiles = [$mediaFiles];
                }
                $validatedData['media'] = $mediaFiles;

            } else {
                $validatedData['media'] = [];
            }

            $postId = $this->postFactory->createPost($validatedData);
            return response()->json(['post_id' => $postId], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Post creation failed ' . $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        try {

            $criteria = $request->only(['search', 'filter', 'sort_by', 'sort_order', 'page', 'page_size']);
            $result = $this->postFactory->getPosts($criteria);

            $posts = $result['data'];
            $total = $result['total'];
            $currentPage = $result['current_page'];
            $lastPage = $result['last_page'];

            return response()->json([
            'data' => PostResource::collection($posts),
            'meta' => [
                'total' => (int) $total,
                'current_page' => (int) $currentPage,
                'last_page' => (int) $lastPage,
                'page_size' => $criteria['page_size'] ?? 10,
            ],
        ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch posts ' . $e->getMessage()], 500);
        }
    }

    public function show($id, Request $request)
    {
        try {
            $post = Controller::findEntity('post', $id);
            return response()->json((new PostResource($post))->toDetailArray($request), 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch post ' . $e->getMessage()], 500);
        }
    }

    public function signPetition($id, CommentRequest $request)
    {
        Controller::findEntity('post', $id);

        if ($this->postFactory->hasUserSigned($id, Auth::id())) {
            return response()->json(['message' => 'You have already signed this post'], 400);
        }

        $validatedData = $request->validated();
        $comment = $validatedData['comment'];

        $this->postFactory->insertSignature($id, Auth::id(), $comment);

        return response()->json(['message' => 'success']);
    }

    public function approveReport($id, CommentRequest $request)
    {
        Controller::findEntity('post', $id);

        if ($this->postFactory->hasUserApproved($id, Auth::id())) {
            return response()->json(['message' => 'You have already approved this report'], 400);
        }

        $validatedData = $request->validated();
        $comment = $validatedData['comment'];

        $this->postFactory->insertApproval($id, Auth::id(), $comment);

        return response()->json(['message' => 'success']);
    }

    public function like($id)
    {
        return $this->toggleAction('post', 'likes', $id);
    }

    public function repost($id)
    {
        return $this->toggleAction('post', 'reposts', $id);
    }

    public function bookmark($id)
    {
        return $this->toggleAction('post', 'bookmarks', $id);
    }


    public function share($id)
    {
        Controller::findEntity('post', $id);

        $shareableUrl = url("/api/posts/{$id}");
        $twitterShareUrl = "https://twitter.com/intent/tweet?url={$shareableUrl}";
        $facebookShareUrl = "https://www.facebook.com/sharer/sharer.php?u={$shareableUrl}";
        $whatsappShareUrl = "whatsapp://send?text={$shareableUrl}";

        return response()->json([
            'shareable_url' => $shareableUrl,
            'twitter_share_url' => $twitterShareUrl,
            'facebook_share_url' => $facebookShareUrl,
            'whatsapp_share_url' => $whatsappShareUrl,
        ]);
    }

}
