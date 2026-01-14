<?php

namespace App\Auth\Support;

use Illuminate\Validation\Rules\Password;

class PasswordRules
{
    public static function default(): array
    {
        $min = (int) config('rbac.password.min_length', 8);

        $rule = Password::min($min);

        $complex = config('rbac.password.complexity', []);

        if (!empty($complex['require_uppercase'])) {
            $rule = $rule->mixedCase();
        }

        if (!empty($complex['require_numbers'])) {
            $rule = $rule->numbers();
        }

        if (!empty($complex['require_special_chars'])) {
            $rule = $rule->symbols();
        }

        if (config('rbac.password.check_common_passwords', false)) {
            $rule = $rule->uncompromised();
        }

        return [$rule];
    }
}