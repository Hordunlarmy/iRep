<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
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

        return [
            'id' => $data->id ?? null,
            'photo_url' => $data->photo_url ?? null,
            'name' => $data->name ?? null,
            'party' => $data->party ?? null,
            'position' => $data->position ?? null,
        ];
    }
}
