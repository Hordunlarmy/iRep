<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyForRepRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'state_id' => 'required|exists:states,id',
            'local_government_id' => 'required|exists:local_governments,id',
            'position_id' => 'required|exists:positions,id',
            'constituency_id' => 'required|exists:constituencies,id',
            'district_id' => 'required|exists:districts,id',
            'party_id' => 'required|exists:parties,id',
            'sworn_in_date' => 'required|date|before:today',
            'social_handles*' => 'required|url',
            'proof_of_office*' => 'required|file|mimes:jpeg,png,jpg,svg,pdf,doc,mp4,mov,avi|max:20480',
        ];
    }

}
