<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\CommentResource;
use App\Jobs\SendNotification;

class CommentController extends Controller
{
    public function create(CommentRequest $request, $postId, $commentId = null)
    {
        try {
            $validatedData = $request->validated();

            $validatedData['postId'] = $postId;
            $validatedData['accountId'] = Auth::id();
            $validatedData['parentId'] = $commentId;

            $commentId = $this->commentFactory->insertComment($validatedData);
            $comment = $this->findEntity('comment', $commentId);

            $notificationData = [
                'entity_id' => $commentId,
                'account_id' => $comment->account_id,
                'post_id' => $comment->post_id,
                'parent_id' => $comment->parent_id,
                'title' => 'New comment on your post',
                'body' => Auth::user()->name . ' commented on your post',
            ];

            SendNotification::dispatch('comment', $notificationData);

            return response()->json(['comment_id' => $commentId], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Comment creation failed ' . $e->getMessage()], 500);
        }
    }

    public function show($id, Request $request)
    {
        $comment = $this->commentFactory->getComment($id);

        if (!$comment) {
            return response()->json([
                'message' => 'Comment not found.'
            ], 404);
        }

        return response()->json((new CommentResource($comment))->toDetailArray($request));
    }

    public function index()
    {
        $comments = $this->commentFactory->getCommentsByUser(Auth::id());

        return response()->json(CommentResource::collection($comments));
    }

    public function like($id)
    {
        return $this->toggleAction('comment', 'likes', $id);
    }

    public function repost($id)
    {
        return $this->toggleAction('comment', 'reposts', $id);
    }

    public function bookmark($id)
    {
        return $this->toggleAction('comment', 'bookmarks', $id);
    }

}
