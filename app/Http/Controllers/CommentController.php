<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\CommentRequest;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function create($id, CommentRequest $request)
    {
        try {
            $validatedData = $request->validated();
            $validatedData['postId'] = $id;
            $validatedData['accountId'] = Auth::id();
            $validatedData['parentId'] = $validatedData['parent_id'] ?? null;

            $commentId = $this->commentFactory->insertComment($validatedData);

            return response()->json(['comment_id' => $commentId], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Comment creation failed ' . $e->getMessage()], 500);
        }
    }

}
