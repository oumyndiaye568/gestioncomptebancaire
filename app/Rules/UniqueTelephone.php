<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueTelephone implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
         if (User::where('telephone', $value)->exists()) {
            $fail('Ce numéro de téléphone est déjà utilisé.');
            return;
        }

        if (!preg_match('/^7[05678]\d{7}$/', $value)) {
            $fail('Le numéro de téléphone n\'est pas valide.');
        }
    }
    
}
