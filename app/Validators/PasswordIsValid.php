<?php

namespace App\Validators;

use Closure;
use Illuminate\Contracts\Validation\InvokableRule;
use Illuminate\Support\Facades\Hash;

class PasswordIsValid implements InvokableRule
{
    public bool $implicit = true;

    public function __construct(protected string $hashedPassword)
    {
    }

    public function __invoke($attribute, $value, $fail): void
    {
        if (!Hash::check($value, $this->hashedPassword)) {
            $fail('Password does not match.');
        }
    }
}
