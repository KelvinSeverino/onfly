<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->isMethod('post')) {
            return [
                'name'          => 'required|string|max:255',
                'email'         => 'required|email|unique:users,email',
                'password'      => 'required|string|min:6',
                'role'          => 'required|string|in:admin,user'
            ];
        }

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            return [
                'name'          => 'sometimes|required|string|max:255',
                'email'         => [
                                    'sometimes',
                                    'required',
                                    'string',
                                    'max:40',
                                    Rule::unique('users', 'email')->ignore($this->route('user'),'id'), 
                                ],
                'password'      => 'nullable|string|min:6',
                'role'          => 'sometimes|required|string|in:admin,user',
            ];
        }

        return [];
    }
}

