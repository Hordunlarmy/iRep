<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/*
* Class to validate the request to create an account
*/
class OnboardAccountRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    /*
    * Rules to validate the request
    */
    public function rules()
    {
        $user = Auth::user();

        $accountType = DB::table('account_types')
            ->where('id', $user->account_type)
            ->value('name');

        return [
            'name' => 'required|string|max:255',
            'dob' => 'required|date|before:today',
            'gender' => 'required|string|in:male,female,other',
            'state' => 'required|string|max:255',
            'local_government' => 'required|string|max:255',
            'kyc' => 'required_if:$accountType,representative|file|mimes:jpeg,png,jpg,gif,svg,mp4,mov,avi,flv,wmv,3gp,webm|max:20480',
            'position' => 'required_if:$accountType,representative|string|max:255',
            'party' => 'required_if:$accountType,representative|string|max:255',
            'constituency' => 'required_if:$accountType,representative|string|max:255',
            'social_handles' => 'required_if:$accountType,representative|json',
        ];
    }

    /*
    * Custom validation error message
    */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'validation_error',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /*
    * Custom authorization error message
    */
    protected function failedAuthorization()
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'authorization_error',
                'message' => 'You are not authorized to perform this action.'
            ], 403)
        );
    }

}
