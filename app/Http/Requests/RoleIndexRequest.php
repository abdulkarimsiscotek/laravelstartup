<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RoleIndexRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:120'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'protected' => ['nullable', 'boolean'],
        ];
    }
}