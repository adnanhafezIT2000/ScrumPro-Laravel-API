<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [

            'role_id'=> ['required'] ,
            'category_id'=>[] ,
            'full_name' => ['required'] ,
            'email' => ['required', 'unique:users'] ,
            'password'=>[] ,
            'phone' => ['required'] ,
            'address' => ['required'] ,
            'birthday' => ['required'] ,
            'gender' => ['required'] ,
            'hourly_rate'=>[] ,
            'rank'=>[]
        ];
    }
}
