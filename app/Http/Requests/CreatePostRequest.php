<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePostRequest extends FormRequest
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
            'post_type' => 'required|in:petition,eyewitness',
            'title' => 'required|string|max:255|unique:posts,title',
            'context' => 'required|string',
            'media.*' => 'nullable|file|mimes:jpeg,png,jpg,mp4,mov,avi|max:20480',
            'target_signatures' => 'nullable|integer|min:1',
            'target_representative_id' => 'required_if:post_type,petition|exists:representatives,id',
            'category' => 'required_if:post_type,eyewitness|in:crime,accident,other',
        ];
    }

    public function messages()
    {
        return [
            'title.required' => 'A title is required for the post.',
            'context.required' => 'A context is required for the post.',
            'target_representative_id.required' => 'You must specify a valid representative as the target.',
            'category.required' => 'You must specify a category for the post.',
            'category.in' => 'The category must be one of crime, accident, or other.',
        ];
    }

}
