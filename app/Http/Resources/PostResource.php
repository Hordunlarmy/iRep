<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class PostResource extends JsonResource
{
    public function toArray($request)
    {
        $data = is_object($this->resource) ? $this->resource : (object) $this->resource;
        $postData = property_exists($data, 'post_data') ? json_decode($data->post_data) : null;

        $commentCount = DB::table('comments')
            ->where('post_id', $data->id)
            ->count() ?? 0;

        $likesCount = DB::table('likes')
            ->where('entity_id', $data->id)
            ->count() ?? 0;

        $repostsCount = DB::table('reposts')
            ->where('entity_id', $data->id)
            ->count() ?? 0;

        $bookmarksCount = DB::table('bookmarks')
            ->where('entity_id', $data->id)
            ->count() ?? 0;

        $badge = 0;
        if ($data->author_kyced) {
            $badge = $data->author_account_type === '1'
                ? 1
                : ($data->author_account_type === '2' ? 2 : 0);
        }

        $responseArray = [
            'id' => $data->id,
            'title' => $data->title,
            'context' => $data->context,
            'post_type' => $data->post_type,
            'author' => $data->author,
            'author_badge' => $badge,
            'reported' => $data->reported,
            'status' => $postData->status ?? null,
            'created_at' => $data->created_at,
            'media' => property_exists($data, 'media') ? json_decode($data->media, true) : null,
            'comments' => $commentCount,
            'likes' => $likesCount,
            'reposts' => $repostsCount,
            'bookmarks' => $bookmarksCount,
        ];

        if ($data->post_type === 'petition') {

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
            ->leftJoin('accounts', 'comments.account_id', '=', 'accounts.id')
            ->where('post_id', $responseArray['id'])
            ->whereNull('parent_id')
            ->select('comments.*', 'accounts.id AS author_id', 'accounts.name AS author_name', 'accounts.photo_url AS author_photo_url')
            ->get();

        if (isset($postData['approvals'])) {
            $responseArray['approvals'] = $postData['approvals'];
        }

        if (isset($postData['category'])) {
            $responseArray['category'] = $postData['category'];
        }

        if (isset($postData['target_representative'])) {
            $responseArray['target_representative'] = $postData['target_representative'];
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

        if ($comments->isNotEmpty()) {
            $nestedComments = $comments->map(function ($comment) use ($request) {
                return (new CommentResource($comment))->toDetailArray($request);
            });

            $responseArray['comments'] = $nestedComments;
        }
        return $responseArray;
    }
}
