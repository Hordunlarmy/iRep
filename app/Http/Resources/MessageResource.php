<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class MessageResource extends JsonResource
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
            'sender' => $data->sender,
            'receiver' => $data->receiver,
            'message' => $data->message,
            'sent_at' => $data->sent_at,
            'read_at' => $data->read_at,
            'edited_at' => $data->edited_at,
        ];


        return $responseArray;
    }
}
