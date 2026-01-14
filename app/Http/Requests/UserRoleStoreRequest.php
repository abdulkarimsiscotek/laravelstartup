<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRoleStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'role_ids' => ['required','array','min:1'],
            'role_ids.*' => ['integer','exists:roles,id'],
        ];
    }
}