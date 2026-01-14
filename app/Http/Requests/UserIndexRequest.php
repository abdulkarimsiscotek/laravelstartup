<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // route middleware already protects
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],          // name/email search
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],

            'role' => ['nullable', 'string', 'max:64'],       // role slug
            'with_trashed' => ['nullable', 'boolean'],
            'only_trashed' => ['nullable', 'boolean'],

            'suspended' => ['nullable', 'boolean'],           // true=only suspended, false=only not
        ];
    }
}