<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

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
            'photo_url' => $data->photo_url ?? null,
            'cover_photo_url' => $data->cover_photo_url ?? null,
            'name' => $data->name ?? null,
            'phone_number' => $data->phone_number ?? null,
            'email' => $data->email ?? null,
            'gender' => $data->gender ?? null,
            'dob' => $data->dob ?? null,
            'state' => $data->state ?? null,
            'local_government' => $data->local_government ?? null,
            'polling_unit' => $data->polling_unit ?? null,
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
            'account_type' => $responseArray['account_type'],
            'photo_url' => $responseArray['photo_url'] ?? null,
            'cover_photo_url' => $responseArray['cover_photo_url'] ?? null,
            'name' => $responseArray['name'] ?? null,
            'email' => $responseArray['email'] ?? null,
            'phone_number' => $responseArray['phone_number'] ?? null,
            'location' => $responseArray['location'] ?? null,
            'local_government' => $responseArray['local_government'] ?? null,
        ];

        $request->merge(['filter' => 'petition']);
        $petitionResponse = \app('App\Http\Controllers\PostController')->getUserPosts($request);
        $petition = $petitionResponse->original['data'] ?? $petitionResponse;

        $request->merge(['filter' => 'eyewitness']);
        $postResponse = \app('App\Http\Controllers\PostController')->getUserPosts($request);
        $post = $postResponse->original['data'] ?? $postResponse;

        $repliesResponse = \app('App\Http\Controllers\CommentController')->index($request);
        $replies = $repliesResponse->original ?? $repliesResponse;


        $profileArray['petition'] = $petition;
        $profileArray['post'] = $post;
        $profileArray['replies'] = $replies;
        $profileArray['bookmarks'] = [];

        return $profileArray;
    }

}
