<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into a basic array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_object($this->resource) ? $this->resource : (object) $this->resource;

        $likes = DB::table('likes')
            ->where('entity_id', $data->id)
            ->count() ?? 0;

        return [
            'id' => $data->id,
            'parent_id' => $data->parent_id,
            'post_id' => $data->post_id,
            'author_id' => $data->author_id ?? null,
            'author_name' => $data->author_name ?? null,
            'author_photo' => $data->author_photo_url ?? null,
            'comment' => $data->comment,
            'likes' => $likes,
            'commented_at' => $data->commented_at,
        ];
    }

    /**
     * Transform the resource into a detailed array with replies.
     *
     * @return array<string, mixed>
     */
    public function toDetailArray(Request $request): array
    {
        $data = is_object($this->resource) ? $this->resource : (object) $this->resource;

        $likes = DB::table('likes')
            ->where('entity_id', $data->id)
            ->count() ?? 0;

        $replies = DB::table('comments')
            ->leftJoin('accounts', 'comments.account_id', '=', 'accounts.id')
            ->where('comments.parent_id', $data->id)
            ->orderBy('commented_at')
            ->select('comments.*', 'accounts.id AS author_id', 'accounts.name AS author_name', 'accounts.photo_url AS author_photo_url')
            ->get();


        $nestedReplies = $replies->map(function ($reply) use ($request) {
            return (new CommentResource($reply))->toDetailArray($request);
        });

        $responseArray = [
            'id' => $data->id,
            'parent_id' => $data->parent_id,
            'post_id' => $data->post_id,
            'author_id' => $data->author_id ?? null,
            'author_name' => $data->author_name ?? null,
            'author_photo' => $data->author_photo_url ?? null,
            'comment' => $data->comment,
            'likes' => $likes,
            'commented_at' => $data->commented_at,
            'replies' => $nestedReplies,
        ];

        return $responseArray;
    }
}
