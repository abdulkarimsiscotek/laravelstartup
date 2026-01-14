<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PrivilegeStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:100'],
            'slug' => ['required','string','max:150','unique:privileges,slug'],
            'description' => ['nullable','string','max:255'],
        ];
    }
}