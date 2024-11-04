<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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

        $responseArray = [
            'id' => $data->id,
            'parent_id' => $data->parent_id,
            'post_id' => $data->post_id,
            'comment' => $data->comment,
            'commented_at' => $data->commented_at,
        ];


        return $responseArray;
    }
}
