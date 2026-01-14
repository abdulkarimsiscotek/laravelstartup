<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:120'],
            'email' => ['required','email','max:190','unique:users,email'],
            // 'password' => ['required','string','min:' . config('rbac.password.min_length', 8)],
            'password' => array_merge(['required'], \App\Auth\Support\PasswordRules::default()),
        ];
    }
}
