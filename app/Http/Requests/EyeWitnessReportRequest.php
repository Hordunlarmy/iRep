<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class EyeWitnessReportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->email_verified;
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
            'category' => 'nullable|string|in:crime,accident,other',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'A title is required for the petition.',
            'description.required' => 'A description is required for the petition.',
            'category.required' => 'A category is required for the report.',
            'category.in' => 'The selected category is invalid. Allowed values are accident, crime, other.'
        ];
    }
}
