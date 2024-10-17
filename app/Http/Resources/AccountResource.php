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
        $accountData = isset($data->account_data) ? json_decode($data->account_data, true) : null;

        $responseArray = [
            'id' => $data->id,
            'account_type' => $data->account_type,
            'photo_url' => $data->photo_url,
            'cover_photo_url' => $data->cover_photo_url,
            'name' => $data->name,
            'phone_number' => $data->phone_number,
            'email' => $data->email,
            'gender' => $data->gender,
            'dob' => $data->dob,
            'state' => $data->state,
            'local_government' => $data->local_government,
            'polling_unit' => $data->polling_unit,
            'created_at' => $data->created_at,
        ];

        if (isset($accountData['occupation'])) {
            $responseArray['occupation'] = $accountData['occupation'];
        }

        if (isset($accountData['location'])) {
            $responseArray['location'] = $accountData['location'];
        }

        if (isset($accountData['position'])) {
            $responseArray['position'] = $accountData['position'];
        }

        if (isset($accountData['constituency'])) {
            $responseArray['constituency'] = $accountData['constituency'];
        }

        if (isset($accountData['party'])) {
            $responseArray['party'] = $accountData['party'];
        }

        return $responseArray;

    }

    public function toProfileArray($request)
    {
        $responseArray = $this->toArray($request);

        $profileArray = [
            'id' => $responseArray['id'],
            'account_type' => $responseArray['account_type'],
            'photo_url' => $responseArray['photo_url'],
            'cover_photo_url' => $responseArray['cover_photo_url'],
            'name' => $responseArray['name'],
            'email' => $responseArray['email'],
        ];

        $request->merge(['filter' => 'petition']);
        $petitionResponse = \app('App\Http\Controllers\PostController')->getUserPosts($request);
        $petition = $petitionResponse->original['data'] ?? $petitionResponse;

        $request->merge(['filter' => 'representative']);
        $postResponse = \app('App\Http\Controllers\PostController')->getUserPosts($request);
        $post = $postResponse->original['data'] ?? $postResponse;

        $profileArray['petition'] = $petition;
        $profileArray['post'] = $post;

        return $profileArray;
    }
}
