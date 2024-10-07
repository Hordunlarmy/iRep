<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/*
* Class to validate the request to create an account
*/
class CreateAccountRequest extends FormRequest
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
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:accounts,email|max:255',
            'phone_number' => 'required|string|unique:accounts,phone_number|max:20',
            'dob' => 'required|date|before:today',
            'state' => 'required|string|max:255',
            'local_government' => 'required|string|max:255',
            'password' => 'required|string|min:8|',
            'account_type' => 'required|string|exists:account_types,name',
            'occupation' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'party' => 'nullable|string|max:255',
            'constituency' => 'nullable|string|max:255',

            //'occupation' => 'required_if:account_type,citizen|string|max:255',
            //'location' => 'required_if:account_type,citizen|string|max:255',
            //'position' => 'required_if:account_type,representative|string|max:255',
            //'party' => 'required_if:account_type,representative|string|max:255',
            //'constituency' => 'required_if:account_type,representative|string|max:255',

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
