<?php

namespace App\Http\Resources;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Resources\Json\JsonResource;

class EyeWitnessReportResource extends JsonResource
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

        $comments = DB::table('eye_witness_reports_comments')
            ->where('report_id', $data->id)
            ->get();

        return [
            'id' => $data->id ?? null,
            'title' => $data->title ?? null,
            'description' => $data->description ?? null,
            'creator_id' => $data->creator_id ?? null,
            'approvals' => $data->approvals ?? 0,
            'comments' => $comments,
            'created_at' => $data->created_at ?? null,
            'updated_at' => $data->updated_at ?? null,
        ];
    }
}
