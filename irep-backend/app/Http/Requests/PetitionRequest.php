<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PetitionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->email_verified;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'target_representative_id' => 'required|integer|exists:representatives,account_id',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'A title is required for the petition.',
            'description.required' => 'A description is required for the petition.',
            'target_representative_id.required' => 'You must specify a valid representative as the target.',
        ];
    }
}
