<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->route('user')?->id;

        return [
            'name' => ['sometimes','string','max:120'],
            'email' => [
                'sometimes','email','max:190',
                Rule::unique('users','email')->ignore($userId),
            ],
            // 'password' => ['sometimes','string','min:' . config('rbac.password.min_length', 8)],
            'password' => array_merge(['sometimes'], \App\Auth\Support\PasswordRules::default()),

        ];
    }
}
