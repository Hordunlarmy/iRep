<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class HomePageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toRepArray()
    {
        // Handle both object and array data
        $data = is_object($this->resource) ? $this->resource : (object) $this->resource;
        $accountData = property_exists($data, 'account_data') ? json_decode($data->account_data) : null;

        if (isset($accountData->position)) {
            $data->position = $accountData->position;
        }
        if (isset($accountData->constituency)) {
            $data->constituency = $accountData->constituency;
        }
        if (isset($accountData->party)) {
            $data->party = $accountData->party;
        }
        if (isset($accountData->district)) {
            $data->district = $accountData->district;
        }
        if (isset($accountData->bio)) {
            $data->bio = $accountData->bio;
        }

        $responseArray = [
            'id' => $data->id,
            'account_type' => $data->account_type,
            'photo_url' => $data->photo_url ?? null,
            'name' => $data->name,
            'state' => $data->state ?? null,
            'local_government' => $data->local_government ?? null,
            'position' => $data->position ?? null,
            'constituency' => $data->constituency ?? null,
            'party' => $data->party ?? null,
            'district' =>  $data->district ?? null,
            'bio' => $data->bio ?? null,
        ];

        $filteredArray = app('utils')->filterNullValues([$responseArray]);

        return $filteredArray;


    }

    public function toPostArray()
    {
        $data = is_object($this->resource) ? $this->resource : (object) $this->resource;
        $postData = property_exists($data, 'post_data') ? json_decode($data->post_data) : null;
        if (isset($postData->signatures)) {
            $data->signatures = $postData->signatures;
        }
        if (isset($postData->target_signatures)) {
            $data->target_signatures = $postData->target_signatures;
        }

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
            $accountType = (int)$data->author_account_type;

            if ($accountType === 1) {
                $badge = 1;
            } elseif ($accountType === 2) {
                $badge = 2;
            }
        }

        $responseArray = [
            'id' => $data->id,
            'title' => $data->title,
            'context' => $data->context,
            'post_type' => $data->post_type,
            'author' => $data->author,
            'author_id' => $data->author_id ?? null,
            'author_badge' => $badge,
            'author_photo_url' => $data->author_photo_url,
            'reported' => $data->reported ?? null,
            'status' => $postData->status ?? null,
            'created_at' => $data->created_at,
            'media' => property_exists($data, 'media') ? json_decode($data->media, true) : null,
            'comments' => $commentCount,
            'likes' => $likesCount,
            'reposts' => $repostsCount,
            'bookmarks' => $bookmarksCount,
            'signatures' => $data->signatures ?? null,
            'target_signatures' => $data->target_signatures ?? null,
        ];

        $filteredArray = app('utils')->filterNullValues([$responseArray]);

        return $filteredArray;

    }
}
