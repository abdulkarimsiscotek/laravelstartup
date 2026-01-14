<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrivilegeUpdateRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $privilegeId = $this->route('privilege')?->id;

        return [
            'name' => ['sometimes','string','max:100'],
            'slug' => [
                'sometimes','string','max:150',
                Rule::unique('privileges','slug')->ignore($privilegeId),
            ],
            'description' => ['nullable','string','max:255'],
        ];
    }
}