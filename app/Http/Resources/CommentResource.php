<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = is_object($this->resource) ? $this->resource : (object) $this->resource;
        $likes = DB::table('likes')
            ->where('entity_id', $data->id)
            ->count() ?? 0;


        $responseArray = [
            'id' => $data->id,
            'parent_id' => $data->parent_id,
            'post_id' => $data->post_id,
            'comment' => $data->comment,
            'likes' => $likes,
            'commented_at' => $data->commented_at,
        ];


        return $responseArray;
    }
}
