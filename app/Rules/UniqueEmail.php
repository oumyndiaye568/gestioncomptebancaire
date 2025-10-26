<?php

namespace App\Rules;

use App\Models\Client;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueEmail implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         if (Client::where('email', $value)->exists()) {
            $fail('Cet email est déjà utilisé.');
        }
    }
    
}
