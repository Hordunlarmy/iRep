<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        $commentCount = DB::table('comments')
            ->where('post_id', $this->id)
            ->count() ?? 0;

        $likesCount = DB::table('likes')
            ->where('entity_id', $this->id)
            ->count() ?? 0;

        $repostsCount = DB::table('reposts')
            ->where('entity_id', $this->id)
            ->count() ?? 0;

        $bookmarksCount = DB::table('bookmarks')
            ->where('entity_id', $this->id)
            ->count() ?? 0;

        $postType = $this->post_type;

        $responseArray = [
            'id' => $this->id,
            'title' => $this->title,
            'context' => $this->context,
            'post_type' => $this->post_type,
            'creator_id' => $this->creator_id,
            'created_at' => $this->created_at,
            'media' => json_decode($this->media, true),
            'comments' => $commentCount,
            'likes' => $likesCount,
            'reposts' => $repostsCount,
            'bookmarks' => $bookmarksCount,
        ];

        if ($postType === 'petition') {
            $postData = json_decode($this->post_data);

            if ($postData && isset($postData->signatures) && isset($postData->target_signatures)) {
                $responseArray['signatures'] = $postData->signatures;
                $responseArray['target_signatures'] = $postData->target_signatures;
            }
        }
        return $responseArray;
    }

    public function toDetailArray($request)
    {
        $postData = json_decode($this->post_data, true);

        $responseArray = $this->toArray($request);

        $comments = DB::table('comments')
            ->where('post_id', $responseArray['id'])
            ->get();

        if (isset($postData['approvals'])) {
            $responseArray['approvals'] = $postData['approvals'];
        }

        if (isset($postData['category'])) {
            $responseArray['category'] = $postData['category'];
        }

        if (isset($postData['target_representative_id'])) {
            $responseArray['target_representative_id'] = $postData['target_representative_id'];
        }

        if (isset($postData['signatures'])) {
            $responseArray['signatures'] = $postData['signatures'];
        }

        if (isset($postData['target_signatures'])) {
            $responseArray['target_signatures'] = $postData['target_signatures'];
        }

        if (isset($postData['status'])) {
            $responseArray['status'] = $postData['status'];
        }

        if (isset($comments)) {
            $responseArray['comments'] = $comments;
        }

        return $responseArray;
    }
}
