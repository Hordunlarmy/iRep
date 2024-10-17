<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HomePageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        // Handle both object and array data
        $data = is_object($this->resource) ? $this->resource : (object) $this->resource;

        \Illuminate\Support\Facades\Log::info('HomePageResource data: ' . json_encode($data));

        return [
            'id' => $data->id,
            'account_type' => $data->account_type,
            'photo_url' => $data->photo_url,
            'name' => $data->name,
            'state' => $data->state,
            'local_government' => $data->local_government,
            'position' => $data->position,
            'party' => $data->party,
            'constituency' => $data->constituency,
        ];

    }
}
