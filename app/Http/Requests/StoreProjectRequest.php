<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'team_id'=> ['required'] ,
            'name'=>['required'],
            'actual_termination_date'=>[],
            'planned_termination_date'=>['required'],
            'budget'=>['required'],
            'description'=>['required'],
            'owner_id'=>['required']

        ];
    }
}
